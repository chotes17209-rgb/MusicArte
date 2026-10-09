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

        if ($error = $this->duplicado($alumno->id, $data)) {
            return $error;
        }

        $taller = DB::transaction(function () use ($alumno, $data, $request) {
            $taller = AlumnoTaller::create([
                'alumno_id' => $alumno->id,
                'especialidad_id' => $data['especialidad_id'],
                'maestro_id' => $data['maestro_id'] ?? null,
                'periodo_id' => $data['periodo_id'] ?? null,
                'salon' => $data['salon'] ?? null,
                'estado' => $data['estado'] ?? 'activo',
            ] + AlumnoTaller::resolverMensualidad($data, $request->input('horarios')));

            if ($request->filled('horarios')) {
                HorarioService::generar($taller, $request->input('horarios'));
            }

            $taller->sincronizarPago();

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

        if (($data['estado'] ?? $alumnoTaller->estado) === 'activo' && ($error = $this->duplicado($alumnoTaller->alumno_id, $data, $alumnoTaller->id))) {
            return $error;
        }
        $periodoAnteriorId = $alumnoTaller->periodo_id;
        $estadoAnterior = $alumnoTaller->estado;
        $estadoNuevo = $data['estado'] ?? $estadoAnterior;

        DB::transaction(function () use ($alumnoTaller, $data, $request, $estadoAnterior, $estadoNuevo) {
            $alumnoTaller->update([
                'especialidad_id' => $data['especialidad_id'],
                'maestro_id' => $data['maestro_id'] ?? null,
                'periodo_id' => $data['periodo_id'] ?? null,
                'salon' => $data['salon'] ?? null,
            ] + AlumnoTaller::resolverMensualidad($data, $request->input('horarios')));

            if ($estadoNuevo === 'inactivo') {
                // Pasarlo a inactivo es lo mismo que darlo de baja.
                $alumnoTaller->darDeBaja();

                return;
            }

            if ($estadoAnterior === 'inactivo') {
                $alumnoTaller->reactivar();
            }

            if ($request->filled('horarios')) {
                HorarioService::generar($alumnoTaller, $request->input('horarios'));
            }

            $alumnoTaller->sincronizarPago();
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
     * "Quitar" un taller no borra el historial: lo da de baja (ver
     * AlumnoTaller::darDeBaja). Las clases dictadas y los pagos con
     * abonos quedan intactos.
     */
    public function destroy(AlumnoTaller $alumnoTaller)
    {
        $periodoId = $alumnoTaller->periodo_id;

        DB::transaction(fn () => $alumnoTaller->darDeBaja());

        $alumno = $alumnoTaller->alumno;
        $alumno->sincronizarTallerPrincipal();
        $alumno->sincronizarEstadoPeriodo($periodoId);

        return response()->json([
            'ok' => true,
            'message' => 'Taller dado de baja para este alumno (se conserva su historial).',
        ]);
    }

    /**
     * Un alumno no puede tener dos veces el mismo taller (misma especialidad y
     * maestro) activo en el mismo periodo: saldria repetido en asistencia,
     * horarios y pagos.
     */
    private function duplicado(int $alumnoId, array $data, ?int $exceptoId = null)
    {
        if (empty($data['periodo_id'])) {
            return null;
        }

        $existe = AlumnoTaller::with(['especialidad', 'periodo'])
            ->where('alumno_id', $alumnoId)
            ->where('periodo_id', $data['periodo_id'])
            ->where('especialidad_id', $data['especialidad_id'])
            ->where('maestro_id', $data['maestro_id'] ?? null)
            ->where('estado', 'activo')
            ->when($exceptoId, fn ($q) => $q->whereKeyNot($exceptoId))
            ->first();

        if (! $existe) {
            return null;
        }

        return response()->json([
            'ok' => false,
            'message' => 'Este alumno ya tiene '.($existe->especialidad->nombre ?? 'ese taller').' con ese maestro en '.($existe->periodo->nombre ?? 'ese periodo').'. Edita ese taller en lugar de agregar otro.',
        ], 422);
    }

    private function validarTaller(Request $request): array
    {
        return $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'periodo_id' => 'nullable|exists:periodos,id',
            'salon' => 'nullable|string|max:50',
            'estado' => 'nullable|in:activo,inactivo',
            'veces_semana' => 'nullable|integer|between:1,7',
            'monto_mensual' => 'nullable|required_with:periodo_id|numeric|min:0|max:99999',
            'nota_mensualidad' => 'nullable|string|max:255',
        ], [
            'especialidad_id.required' => 'Selecciona el taller (especialidad).',
            'monto_mensual.required_with' => 'Escribe la mensualidad que pagará el alumno por este taller.',
            'monto_mensual.numeric' => 'La mensualidad debe ser un monto válido.',
        ]);
    }
}