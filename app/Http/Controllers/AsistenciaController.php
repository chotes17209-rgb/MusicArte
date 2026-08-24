<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Clase;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $clases = Clase::with(['alumno', 'maestro', 'especialidad', 'asistencia'])
            ->whereDate('fecha', $fecha)
            ->orderBy('hora_inicio')
            ->get();

        $alumnos = Alumno::activos()->orderBy('nombre')->get();

        // Reporte rapido: % de asistencia del alumno seleccionado (ultimos 30 dias)
        $resumenAlumno = null;
        if ($request->filled('alumno_id')) {
            $alumno = Alumno::find($request->alumno_id);
            if ($alumno) {
                $total = $alumno->asistencias()->count();
                $asistio = $alumno->asistencias()->where('estado', 'asistio')->count();
                $resumenAlumno = [
                    'alumno' => $alumno->nombre,
                    'total' => $total,
                    'asistio' => $asistio,
                    'porcentaje' => $total > 0 ? round($asistio / $total * 100, 1) : 0,
                    'detalle' => $alumno->asistencias()->with('clase')->latest()->limit(20)->get(),
                ];
            }
        }

        return view('asistencia.index', compact('clases', 'alumnos', 'fecha', 'resumenAlumno'));
    }

    /** Marcar/actualizar asistencia de una clase puntual (modal rapido). */
    public function marcar(Request $request, Clase $clase)
    {
        $data = $request->validate([
            'estado' => 'required|in:asistio,falto,justificado,tardanza',
            'observacion' => 'nullable|string',
        ]);

        $asistencia = Asistencia::updateOrCreate(
            ['clase_id' => $clase->id, 'alumno_id' => $clase->alumno_id],
            [
                'estado' => $data['estado'],
                'observacion' => $data['observacion'] ?? null,
                'registrado_por' => $request->user()->id,
            ]
        );

        if ($clase->estado === 'programada') {
            $clase->update(['estado' => 'realizada']);
        }

        return response()->json(['ok' => true, 'message' => 'Asistencia registrada correctamente.', 'data' => $asistencia]);
    }
}
