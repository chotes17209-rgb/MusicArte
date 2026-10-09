<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoPeriodo;
use App\Models\AlumnoTaller;
use App\Models\Periodo;
use App\Services\PaseDePeriodoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeriodoController extends Controller
{
    /**
     * Periodos ya no tiene pantalla propia (seccion movida dentro de
     * Alumnos, pestana "Periodos"). Esta ruta se conserva solo por
     * compatibilidad con enlaces/favoritos antiguos y redirige alla.
     */
    public function index()
    {
        return redirect()->route('alumnos.index', ['tab' => 'periodos']);
    }

    /**
     * Cambia el periodo con el que se trabaja en toda la app (selector de
     * la barra superior). Vacio = volver al periodo en curso.
     */
    public function seleccionar(Request $request)
    {
        $request->validate(['periodo_id' => 'nullable|exists:periodos,id']);

        if ($request->filled('periodo_id')) {
            session(['periodo_id' => (int) $request->periodo_id]);
        } else {
            session()->forget('periodo_id');
        }

        // Al volver, se quitan los filtros de mes/periodo de la URL para que
        // la pantalla tome el periodo recien elegido.
        $anterior = url()->previous();
        $partes = parse_url($anterior);
        parse_str($partes['query'] ?? '', $query);
        unset($query['periodo_id'], $query['mes'], $query['anio'], $query['page']);
        $destino = ($partes['path'] ?? '/').($query ? '?'.http_build_query($query) : '');

        return redirect($destino);
    }

    /** Ver: resumen del periodo (inscritos, clases, asistencia y pagos). */
    public function show(Periodo $periodo)
    {
        $talleres = \App\Models\AlumnoTaller::with(['especialidad', 'maestro'])->where('periodo_id', $periodo->id)->get();
        $clases = \App\Models\Clase::where('periodo_id', $periodo->id)->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $asistencias = \App\Models\Asistencia::whereHas('clase', fn ($q) => $q->where('periodo_id', $periodo->id))
            ->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $pagos = \App\Models\Pago::where('mes', $periodo->mes)->where('anio', $periodo->anio)->get();

        $resumen = [
            'alumnos' => $talleres->pluck('alumno_id')->unique()->count(),
            'talleres' => $talleres->count(),
            'clases' => $clases->sum(),
            'dictadas' => $clases['realizada'] ?? 0,
            'asistencia' => $asistencias->sum() > 0 ? round((($asistencias['asistio'] ?? 0) + ($asistencias['tardanza'] ?? 0) + ($asistencias['recupero'] ?? 0)) / $asistencias->sum() * 100) : null,
            'pagos_total' => $pagos->count(),
            'pagos_pagados' => $pagos->where('estado', 'pagado')->count(),
        ];
        $porEspecialidad = $talleres->groupBy(fn ($t) => $t->especialidad->nombre ?? '—')
            ->map(fn ($ts) => ['alumnos' => $ts->pluck('alumno_id')->unique()->count(), 'maestros' => $ts->pluck('maestro.nombre')->filter()->unique()->sort()->implode(', '), 'color' => $ts->first()->especialidad->color ?? '#999'])
            ->sortByDesc('alumnos');

        return view('periodos.show', compact('periodo', 'resumen', 'porEspecialidad'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $periodo = Periodo::create($data);

        return response()->json(['ok' => true, 'message' => "Periodo '{$periodo->nombre}' creado correctamente.", 'data' => $periodo]);
    }

    public function edit(Periodo $periodo)
    {
        return response()->json(['ok' => true, 'data' => $periodo]);
    }

    public function update(Request $request, Periodo $periodo)
    {
        $data = $this->validarDatos($request, $periodo->id);
        $periodo->fill($data);

        // Reabierto o con la fecha de fin extendida: vuelve a cerrarse solo cuando venza.
        if ($periodo->activo && ! $periodo->haTerminado()) {
            $periodo->cerrado_en = null;
        }

        $periodo->save();

        return response()->json(['ok' => true, 'message' => 'Periodo actualizado correctamente.', 'data' => $periodo]);
    }

    public function destroy(Periodo $periodo)
    {
        if ($periodo->horarios()->exists() || $periodo->clases()->exists()) {
            return response()->json(['ok' => false, 'message' => 'No se puede eliminar: hay horarios o clases usando este periodo.'], 422);
        }

        $periodo->delete();

        return response()->json(['ok' => true, 'message' => 'Periodo eliminado.']);
    }

    /**
     * Seccion 8: alumnos "candidatos" para pasar al siguiente periodo, es
     * decir, los que estuvieron ACTIVOS en el periodo indicado. Se apoya
     * primero en el historial (alumno_periodo); como respaldo, tambien
     * revisa los talleres activos con ese periodo (por si el historial de
     * ese mes es anterior a que existiera esta funcionalidad).
     */
    public function candidatos(Periodo $periodo)
    {
        $idsDesdeHistorial = AlumnoPeriodo::where('periodo_id', $periodo->id)
            ->where('estado', 'activo')
            ->pluck('alumno_id');

        $idsDesdeTalleres = AlumnoTaller::where('periodo_id', $periodo->id)
            ->where('estado', 'activo')
            ->pluck('alumno_id');

        $ids = $idsDesdeHistorial->merge($idsDesdeTalleres)->unique();

        $alumnos = Alumno::whereIn('id', $ids)->orderBy('nombre')->get(['id', 'nombre']);

        return response()->json(['ok' => true, 'data' => $alumnos]);
    }

    /**
     * Seccion 8: pasa al alumno seleccionado del periodo anterior hacia
     * este periodo (el nuevo). Solo se marcan como candidatos los que
     * estaban activos en el periodo anterior (regla 5); el usuario decide
     * con casillas quienes continuan (regla 6); los no seleccionados
     * quedan explicitamente inactivos en el nuevo periodo, sin borrar su
     * historial del periodo anterior (regla 4).
     *
     * Con "copiar_talleres" (marcado por defecto), a cada alumno que
     * continua se le copian sus talleres del periodo anterior: maestro,
     * dias y horas, modalidad y mensualidad, con sus clases y su pago
     * pendiente del nuevo mes. Luego cualquier cambio (otro maestro, otra
     * modalidad) se hace en "Editar alumno -> Talleres".
     */
    public function pasarAlumnos(Request $request, Periodo $periodo)
    {
        $data = $request->validate([
            'periodo_anterior_id' => 'required|exists:periodos,id',
            'alumno_ids' => 'array',
            'alumno_ids.*' => 'exists:alumnos,id',
            'copiar_talleres' => 'nullable|boolean',
        ], [
            'periodo_anterior_id.required' => 'Selecciona el periodo anterior.',
        ]);

        if ((int) $data['periodo_anterior_id'] === $periodo->id) {
            return response()->json(['ok' => false, 'message' => 'El periodo anterior y el nuevo periodo no pueden ser el mismo.'], 422);
        }

        $seleccionados = collect($data['alumno_ids'] ?? [])->map(fn ($id) => (int) $id);

        $candidatosIds = AlumnoPeriodo::where('periodo_id', $data['periodo_anterior_id'])
            ->where('estado', 'activo')
            ->pluck('alumno_id')
            ->merge(
                AlumnoTaller::where('periodo_id', $data['periodo_anterior_id'])
                    ->where('estado', 'activo')
                    ->pluck('alumno_id')
            )
            ->unique();

        // Quienes continuan vuelven a estar activos si el nuevo periodo aun no termina
        // (al cerrarse el periodo anterior pudieron quedar inactivos).
        if (! $periodo->haTerminado()) {
            Alumno::whereIn('id', $seleccionados)->update(['activo' => true]);
        }

        $copiar = $request->boolean('copiar_talleres', true);
        $origen = Periodo::findOrFail($data['periodo_anterior_id']);
        $totales = ['talleres' => 0, 'clases' => 0, 'pagos' => 0, 'matriculas' => 0];

        DB::transaction(function () use ($candidatosIds, $seleccionados, $periodo, $origen, $copiar, &$totales) {
            foreach ($candidatosIds as $alumnoId) {
                $continua = $seleccionados->contains($alumnoId);

                AlumnoPeriodo::updateOrCreate(
                    ['alumno_id' => $alumnoId, 'periodo_id' => $periodo->id],
                    ['estado' => $continua ? 'activo' : 'inactivo']
                );

                // Año nuevo: la matricula se vuelve a pagar. Se crea pendiente con
                // el mismo monto del año anterior (se puede cambiar al editar al alumno).
                if ($continua && $periodo->anio > $origen->anio) {
                    $alumno = Alumno::find($alumnoId);
                    $anterior = $alumno->matriculaDe($origen->anio);
                    if ($anterior && ! $alumno->matriculaDe($periodo->anio)) {
                        $alumno->registrarMatricula($periodo->anio, $anterior->monto_total, null, $periodo->mes);
                        $totales['matriculas']++;
                    }
                }

                if ($continua && $copiar) {
                    foreach (PaseDePeriodoService::copiarTalleres(Alumno::find($alumnoId), $origen, $periodo) as $clave => $n) {
                        $totales[$clave] += $n;
                    }
                }
            }
        });

        $mensaje = "{$seleccionados->count()} alumno(s) pasaron activos a {$periodo->nombre}.";
        if ($copiar && $totales['talleres']) {
            $mensaje .= " Se copiaron {$totales['talleres']} talleres con {$totales['clases']} clases y se crearon {$totales['pagos']} mensualidades pendientes.";
        } elseif ($copiar) {
            $mensaje .= ' Ya tenían sus talleres en este periodo; no se copió nada nuevo.';
        }
        $mensaje .= " En su historial, {$origen->nombre} ya aparece como inactivo.";
        if ($totales['matriculas']) {
            $mensaje .= " Se crearon {$totales['matriculas']} matrículas {$periodo->anio} pendientes.";
        }

        return response()->json(['ok' => true, 'message' => $mensaje, 'data' => $totales]);
    }

    private function validarDatos(Request $request, $ignoreId = null): array
    {
        $data = $request->validate([
            'mes' => [
                'required', 'integer', 'between:1,12',
                Rule::unique('periodos')->where(fn ($q) => $q->where('anio', $request->anio))->ignore($ignoreId),
            ],
            'anio' => 'required|integer|min:2020|max:2100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'nullable|boolean',
        ], [
            'mes.required' => 'Selecciona el mes.',
            'mes.unique' => 'Ya existe un periodo para ese mes y ano.',
            'anio.required' => 'Indica el ano.',
        ]);

        // Si no se indican fechas exactas, se calculan automaticamente 4 semanas desde el dia 1 del mes.
        if (empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
            [$data['fecha_inicio'], $data['fecha_fin']] = Periodo::sugerirRango($data['mes'], $data['anio']);
        }

        $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                  7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $data['nombre'] = $meses[$data['mes']].' '.$data['anio'];

        return $data;
    }
}