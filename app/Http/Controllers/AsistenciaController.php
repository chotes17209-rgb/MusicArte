<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Maestro;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $clases = Clase::with(['alumno', 'maestro', 'especialidad', 'asistencia'])
            ->sinBajas()
            ->whereDate('fecha', $fecha);

        // 16. Filtro por maestro: solo mostrar los alumnos/clases de ese maestro.
        if ($request->filled('maestro_id')) {
            $clases->where('maestro_id', $request->maestro_id);
        }

        $clases = $clases->orderBy('hora_inicio')->get();

        $alumnos = Alumno::activos()->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        // Reporte rapido: % de asistencia del alumno seleccionado (ultimos 30 dias)
        $resumenAlumno = null;
        if ($request->filled('alumno_id')) {
            $alumno = Alumno::find($request->alumno_id);
            if ($alumno) {
                $total = $alumno->asistencias()->count();
                $asistio = $alumno->asistencias()->whereIn('estado', Asistencia::PRESENTES)->count();
                $resumenAlumno = [
                    'alumno' => $alumno->nombre,
                    'total' => $total,
                    'asistio' => $asistio,
                    'porcentaje' => $total > 0 ? round($asistio / $total * 100, 1) : 0,
                    'detalle' => $alumno->asistencias()->with('clase')->latest()->limit(20)->get(),
                ];
            }
        }

        return view('asistencia.index', compact('clases', 'alumnos', 'maestros', 'fecha', 'resumenAlumno'));
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
