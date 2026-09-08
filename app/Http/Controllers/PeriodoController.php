<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoPeriodo;
use App\Models\AlumnoTaller;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::orderByDesc('anio')->orderByDesc('mes')->get();

        return view('periodos.index', compact('periodos'));
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
        $periodo->update($data);

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
     * IMPORTANTE: esto solo marca la continuidad del alumno. Para asignarle
     * taller/horario dentro del nuevo periodo se usa la pantalla de
     * "Editar alumno -> Talleres" (Fase 2), asi no se copian horarios que
     * el usuario no confirmo.
     */
    public function pasarAlumnos(Request $request, Periodo $periodo)
    {
        $data = $request->validate([
            'periodo_anterior_id' => 'required|exists:periodos,id',
            'alumno_ids' => 'array',
            'alumno_ids.*' => 'exists:alumnos,id',
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

        foreach ($candidatosIds as $alumnoId) {
            AlumnoPeriodo::updateOrCreate(
                ['alumno_id' => $alumnoId, 'periodo_id' => $periodo->id],
                ['estado' => $seleccionados->contains($alumnoId) ? 'activo' : 'inactivo']
            );
        }

        return response()->json([
            'ok' => true,
            'message' => "{$seleccionados->count()} alumno(s) pasaron activos al periodo {$periodo->nombre}.",
        ]);
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