<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Periodo;

/**
 * Pasa los talleres de un alumno de un periodo al siguiente: mismo taller,
 * maestro, salon, dias y horas, modalidad y mensualidad. En el periodo
 * nuevo se generan sus clases y su pago pendiente del mes. Si el alumno ya
 * tiene ese taller en el periodo nuevo, no se duplica.
 */
class PaseDePeriodoService
{
    /** @return array{talleres: int, clases: int, pagos: int} */
    public static function copiarTalleres(Alumno $alumno, Periodo $origen, Periodo $destino): array
    {
        $resultado = ['talleres' => 0, 'clases' => 0, 'pagos' => 0];

        $yaInscritos = $alumno->talleres()->where('periodo_id', $destino->id)->pluck('especialidad_id');

        $talleres = $alumno->talleres()
            ->where('periodo_id', $origen->id)
            ->where('estado', 'activo')
            ->with(['horarios' => fn ($q) => $q->where('periodo_id', $origen->id)->where('activo', true)])
            ->get();

        foreach ($talleres as $anterior) {
            if ($yaInscritos->contains($anterior->especialidad_id)) {
                continue;
            }

            $nuevo = AlumnoTaller::create([
                'alumno_id' => $alumno->id,
                'especialidad_id' => $anterior->especialidad_id,
                'maestro_id' => $anterior->maestro_id,
                'periodo_id' => $destino->id,
                'salon' => $anterior->salon,
                'estado' => 'activo',
                'veces_semana' => $anterior->veces_semana,
                'monto_mensual' => $anterior->monto_mensual,
                'nota_mensualidad' => $anterior->nota_mensualidad,
            ]);
            $resultado['talleres']++;

            $horarios = $anterior->horarios->map(fn ($h) => [
                'dia_semana' => $h->dia_semana,
                'hora_inicio' => substr($h->hora_inicio, 0, 5),
                'hora_fin' => substr($h->hora_fin, 0, 5),
            ])->values()->all();

            if ($horarios) {
                $resultado['clases'] += HorarioService::generar($nuevo, $horarios);
            }

            if ($nuevo->sincronizarPago()?->wasRecentlyCreated) {
                $resultado['pagos']++;
            }

            $yaInscritos->push($anterior->especialidad_id);
        }

        if ($resultado['talleres']) {
            $alumno->sincronizarEstadoPeriodo($destino->id);
            $alumno->sincronizarTallerPrincipal();
        }

        return $resultado;
    }
}
