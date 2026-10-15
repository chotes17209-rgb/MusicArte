<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
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
