<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Services\HorarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoTallerController extends Controller
{
    /**
     * Inscribe al alumno en un taller adicional. Un alumno puede tener
     * varios talleres a la vez, cada uno con su propio maestro, horario,
     * periodo y estado (seccion 3 y 4 del requerimiento).
     */
    public function store(Request $request, Alumno $alumno)
    {
        $data = $this->validarTaller($request);

        $taller = DB::transaction(function () use ($alumno, $data, $request) {
            $taller = AlumnoTaller::create([
                'alumno_id' => $alumno->id,
                'especialidad_id' => $data['especialidad_id'],
                'maestro_id' => $data['maestro_id'] ?? null,
                'periodo_id' => $data['periodo_id'] ?? null,
                'salon' => $data['salon'] ?? null,
                'estado' => $data['estado'] ?? 'activo',
            ]);

            if ($request->filled('horarios')) {
                HorarioService::generar($taller, $request->input('horarios'));
            }

            return $taller;
        });

        $alumno->sincronizarTallerPrincipal();
        $alumno->sincronizarEstadoPeriodo($taller->periodo_id);

        return response()->json([
            'ok' => true,
            'message' => 'Taller agregado al alumno.',
            'data' => $taller->load(['especialidad', 'maestro', 'periodo', 'horarios']),
        ]);
    }

    /**
     * Actualiza los datos de un taller de un alumno (maestro, periodo,
     * salon, estado) y, si se envian dias/horas, regenera su horario.
     */
    public function update(Request $request, AlumnoTaller $alumnoTaller)
    {
        $data = $this->validarTaller($request);
        $periodoAnteriorId = $alumnoTaller->periodo_id;

        DB::transaction(function () use ($alumnoTaller, $data, $request) {
            $alumnoTaller->update([
                'especialidad_id' => $data['especialidad_id'],
                'maestro_id' => $data['maestro_id'] ?? null,
                'periodo_id' => $data['periodo_id'] ?? null,
                'salon' => $data['salon'] ?? null,
                'estado' => $data['estado'] ?? $alumnoTaller->estado,
            ]);

            if ($request->filled('horarios')) {
                HorarioService::generar($alumnoTaller, $request->input('horarios'));
            }
        });

        $alumno = $alumnoTaller->alumno;
        $alumno->sincronizarTallerPrincipal();

        // Si el taller cambio de periodo, hay que recalcular el estado en
        // AMBOS periodos (el anterior pudo quedar sin ningun taller activo).
        $alumno->sincronizarEstadoPeriodo($periodoAnteriorId);
        $alumno->sincronizarEstadoPeriodo($alumnoTaller->periodo_id);

        return response()->json([
            'ok' => true,
            'message' => 'Taller actualizado.',
            'data' => $alumnoTaller->load(['especialidad', 'maestro', 'periodo', 'horarios']),
        ]);
    }

    /**
     * "Quitar" un taller no borra el historial: lo marca inactivo, apaga
     * sus horarios semanales (para que dejen de generar clases nuevas) y
     * cancela las clases futuras que aun no se hayan dictado. Las clases
     * ya realizadas y los pagos asociados quedan intactos.
     */
    public function destroy(AlumnoTaller $alumnoTaller)
    {
        $periodoId = $alumnoTaller->periodo_id;

        DB::transaction(function () use ($alumnoTaller) {
            $alumnoTaller->update(['estado' => 'inactivo']);
            $alumnoTaller->horarios()->update(['activo' => false]);
            $alumnoTaller->clases()
                ->where('fecha', '>=', now()->toDateString())
                ->where('estado', 'programada')
                ->update(['estado' => 'cancelada']);
        });

        $alumno = $alumnoTaller->alumno;
        $alumno->sincronizarTallerPrincipal();
        $alumno->sincronizarEstadoPeriodo($periodoId);

        return response()->json([
            'ok' => true,
            'message' => 'Taller dado de baja para este alumno (se conserva su historial).',
        ]);
    }

    private function validarTaller(Request $request): array
    {
        return $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'periodo_id' => 'nullable|exists:periodos,id',
            'salon' => 'nullable|string|max:50',
            'estado' => 'nullable|in:activo,inactivo',
        ], [
            'especialidad_id.required' => 'Selecciona el taller (especialidad).',
        ]);
    }
}