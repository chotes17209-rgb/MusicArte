<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Maestro;
use App\Models\Periodo;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $clases = Clase::with(['alumno', 'maestro', 'especialidad', 'asistencia'])
            ->sinBajas()
            ->whereDate('fecha', $fecha)
            // Una clase sin marcar solo sale si su horario sigue activo y es de este dia.
            ->where(fn ($q) => $q->where('estado', '!=', 'programada')
                ->orWhereHas('asistencia')
                ->orWhereNull('horario_id')
                ->orWhereHas('horario', fn ($h) => $h->where('activo', true)
                    ->where('dia_semana', \Carbon\Carbon::parse($fecha)->isoWeekday())));

        // 16. Filtro por maestro: solo mostrar los alumnos/clases de ese maestro.
        if ($request->filled('maestro_id')) {
            $clases->where('maestro_id', $request->maestro_id);
        }

        $clases = $clases->orderBy('hora_inicio')->get();

        $alumnos = Alumno::activos()->orWhere('id', $request->alumno_id)->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        // Resumen del alumno elegido: por defecto solo el periodo en curso.
        $periodos = Periodo::orderByDesc('fecha_inicio')->get();
        $periodoResumen = $request->get('periodo_resumen', (string) (Periodo::enCurso()?->id ?? 'todos'));
        $resumenAlumno = null;
        if ($request->filled('alumno_id')) {
            $alumno = Alumno::find($request->alumno_id);
            if ($alumno) {
                $delPeriodo = $alumno->asistencias()
                    ->join('clases', 'clases.id', '=', 'asistencias.clase_id')
                    ->when($periodoResumen !== 'todos', fn ($q) => $q->where('clases.periodo_id', $periodoResumen))
                    ->orderByDesc('clases.fecha')->orderByDesc('clases.hora_inicio')
                    ->select('asistencias.*')
                    ->with('clase.especialidad')
                    ->get();

                // Talleres que tiene en ese periodo, para elegir uno.
                $talleresResumen = $delPeriodo->pluck('clase.especialidad')->filter()->unique('id')->sortBy('nombre')->values();
                $tallerResumen = $request->filled('taller_resumen') && $talleresResumen->contains('id', (int) $request->taller_resumen)
                    ? (int) $request->taller_resumen : null;
                // Agrupado por taller (todo Canto, luego todo Guitarra...) y,
                // dentro de cada taller, de la clase mas reciente a la mas antigua.
                $detalle = ($tallerResumen
                    ? $delPeriodo->filter(fn ($a) => $a->clase?->especialidad_id === $tallerResumen)
                    : $delPeriodo)
                    ->sortBy([
                        fn ($a, $b) => strcmp($a->clase?->especialidad?->nombre ?? '~', $b->clase?->especialidad?->nombre ?? '~'),
                        fn ($a, $b) => strcmp($b->clase?->fecha?->toDateString().$b->clase?->hora_inicio, $a->clase?->fecha?->toDateString().$a->clase?->hora_inicio),
                    ])->values();

                // Asistencia de cada taller por separado.
                $porTaller = $delPeriodo->groupBy(fn ($a) => $a->clase?->especialidad?->nombre ?? 'Sin taller')
                    ->map(function ($items) {
                        $presentes = $items->whereIn('estado', Asistencia::PRESENTES)->count();

                        return [
                            'total' => $items->count(),
                            'asistio' => $presentes,
                            'faltas' => $items->whereIn('estado', ['falto', 'justificado'])->count(),
                            'porcentaje' => $items->count() ? round($presentes / $items->count() * 100) : 0,
                        ];
                    })->sortKeys();
                $total = $detalle->count();
                $asistio = $detalle->whereIn('estado', Asistencia::PRESENTES)->count();
                $resumenAlumno = [
                    'alumno' => $alumno->nombre,
                    'total' => $total,
                    'asistio' => $asistio,
                    'faltas' => $detalle->whereIn('estado', ['falto', 'justificado'])->count(),
                    'porcentaje' => $total > 0 ? round($asistio / $total * 100, 1) : 0,
                    'detalle' => $detalle,
                    'talleres' => $talleresResumen,
                    'taller' => $tallerResumen,
                    'porTaller' => $porTaller,
                ];
            }
        }

        return view('asistencia.index', compact('clases', 'alumnos', 'maestros', 'fecha', 'resumenAlumno', 'periodos', 'periodoResumen'));
    }

    /**
     * Vista del mes, como el Excel de asistencia: una fila por alumno y
     * taller, y sus clases del periodo agrupadas por semana, pintadas segun
     * lo marcado. Sirve para ver de un vistazo quien falto y programar sus
     * recuperaciones.
     */
    public function mes(Request $request)
    {
        $periodos = Periodo::orderByDesc('fecha_inicio')->get();
        $periodo = $periodos->firstWhere('id', (int) $request->get('periodo_id')) ?? Periodo::enCurso() ?? $periodos->first();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();
        $especialidades = \App\Models\Especialidad::orderBy('nombre')->get();

        $filas = collect();
        $semanas = [];
        if ($periodo) {
            // Semanas del periodo (de lunes a domingo).
            $inicioSemana = $periodo->fecha_inicio->copy()->startOfWeek();
            for ($d = $inicioSemana->copy(); $d->lte($periodo->fecha_fin); $d->addWeek()) {
                $semanas[] = ['desde' => $d->copy()->max($periodo->fecha_inicio), 'hasta' => $d->copy()->endOfWeek()->min($periodo->fecha_fin)];
            }

            $talleres = AlumnoTaller::with(['alumno', 'especialidad', 'maestro',
                'horarios' => fn ($q) => $q->where('periodo_id', $periodo->id)->orderBy('dia_semana')])
                // Talleres del periodo, y tambien los que tienen clases de este
                // periodo aunque el taller ya se haya pasado al mes siguiente.
                ->where(fn ($q) => $q->where(fn ($p) => $p->where('periodo_id', $periodo->id)
                        ->where(fn ($e) => $e->where('estado', 'activo')->orWhereHas('clases', fn ($c) => $c->whereHas('asistencia'))))
                    ->orWhereHas('clases', fn ($c) => $c->where('periodo_id', $periodo->id)
                        ->whereDate('fecha', '>=', $periodo->fecha_inicio->toDateString())
                        ->whereDate('fecha', '<=', $periodo->fecha_fin->toDateString())))
                ->when($request->filled('maestro_id'), fn ($q) => $q->where('maestro_id', $request->maestro_id))
                ->when($request->filled('especialidad_id'), fn ($q) => $q->where('especialidad_id', $request->especialidad_id))
                ->get();

            // Solo las clases dentro de las fechas del periodo: un taller que
            // viene de meses anteriores tambien tiene guardadas esas clases.
            $clases = Clase::with('asistencia')->sinBajas()
                ->whereIn('alumno_taller_id', $talleres->pluck('id'))
                ->whereDate('fecha', '>=', $periodo->fecha_inicio->toDateString())
                ->whereDate('fecha', '<=', $periodo->fecha_fin->toDateString())
                ->orderBy('fecha')->orderBy('hora_inicio')
                ->get()->groupBy('alumno_taller_id');

            $ultimaSemana = count($semanas) - 1;
            $filas = $talleres->map(function ($t) use ($clases, $inicioSemana, $ultimaSemana) {
                $suyas = $clases->get($t->id, collect());
                $activos = $t->horarios->where('activo', true);
                $hs = $activos->isNotEmpty() ? $activos : $t->horarios;
                $dias = $hs->map(fn ($h) => $h->diaLabel())->unique()->values();
                $horas = $hs->map(fn ($h) => \Carbon\Carbon::parse($h->hora_inicio)->format('g:i a'))->unique();

                return [
                    'taller' => $t,
                    'horario' => $dias->isEmpty() ? '—'
                        : ($dias->count() > 1 ? $dias->slice(0, -1)->implode(', ').' y '.$dias->last() : $dias->first())
                          .' '.($horas->count() === 1 ? $horas->first() : ''),
                    'porSemana' => $suyas->groupBy(fn ($c) => max(0, min($ultimaSemana, (int) floor($inicioSemana->diffInDays($c->fecha->copy()->startOfDay(), false) / 7)))),
                    'asistio' => $suyas->filter(fn ($c) => in_array($c->asistencia?->estado, Asistencia::PRESENTES))->count(),
                    'faltas' => $suyas->filter(fn ($c) => in_array($c->asistencia?->estado, ['falto', 'justificado']))->count(),
                    'recupero' => $suyas->filter(fn ($c) => $c->asistencia?->estado === 'recupero')->count(),
                    'sinMarcar' => $suyas->filter(fn ($c) => ! $c->asistencia && $c->fecha->lt(today()))->count(),
                ];
            })
                ->when($request->filled('buscar'), function ($f) use ($request) {
                    $q = mb_strtolower(\Illuminate\Support\Str::ascii(trim($request->buscar)));

                    return $f->filter(fn ($x) => str_contains(mb_strtolower(\Illuminate\Support\Str::ascii($x['taller']->alumno->nombre ?? '')), $q));
                })
                ->when($request->boolean('con_faltas'), fn ($f) => $f->filter(fn ($x) => $x['faltas'] > 0))
                ->sortBy([
                    fn ($a, $b) => strcmp($a['taller']->maestro->nombre ?? '~', $b['taller']->maestro->nombre ?? '~'),
                    fn ($a, $b) => strcmp($a['taller']->alumno->nombre ?? '', $b['taller']->alumno->nombre ?? ''),
                ])->values();
        }

        return view('asistencia.mes', compact('periodos', 'periodo', 'maestros', 'especialidades', 'filas', 'semanas'));
    }

    /**
     * Marcar/actualizar la asistencia de una clase: A (asistio), F (falto),
     * R (recupero) o S (sin marcar, borra lo marcado). La observacion solo se
     * cambia cuando se envia; los botones rapidos la conservan.
     */
    public function marcar(Request $request, Clase $clase)
    {
        $data = $request->validate([
            'estado' => 'required|in:asistio,falto,justificado,tardanza,recupero,sin_marcar',
            'observacion' => 'nullable|string|max:1000',
        ]);

        if ($data['estado'] === 'sin_marcar') {
            $clase->asistencia()->delete();
            if ($clase->estado === 'realizada') {
                $clase->update(['estado' => 'programada']);
            }

            return response()->json(['ok' => true, 'message' => 'Asistencia desmarcada.', 'data' => null]);
        }

        $valores = ['estado' => $data['estado'], 'registrado_por' => $request->user()->id];
        if ($request->has('observacion')) {
            $valores['observacion'] = trim((string) $data['observacion']) ?: null;
        }

        $asistencia = Asistencia::updateOrCreate(
            ['clase_id' => $clase->id, 'alumno_id' => $clase->alumno_id],
            $valores
        );

        if ($clase->estado === 'programada') {
            $clase->update(['estado' => 'realizada']);
        }

        return response()->json(['ok' => true, 'message' => 'Asistencia registrada correctamente.', 'data' => $asistencia]);
    }
}
