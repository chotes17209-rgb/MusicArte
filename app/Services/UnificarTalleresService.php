<?php

namespace App\Services;

use App\Models\AlumnoTaller;
use App\Models\Clase;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

/**
 * Junta los talleres repetidos de un alumno: el mismo taller (especialidad
 * y maestro) activo varias veces en el mismo periodo, por ejemplo al
 * presionar "Guardar taller" varias veces. Queda uno solo y no se pierde
 * nada: las clases con asistencia y los abonos pasan al taller que queda.
 */
class UnificarTalleresService
{
    /** @return int cantidad de talleres repetidos eliminados */
    public static function unificarTodo(): int
    {
        $grupos = AlumnoTaller::where('estado', 'activo')->whereNotNull('periodo_id')
            ->select('alumno_id', 'periodo_id', 'especialidad_id', 'maestro_id', DB::raw('count(*) as total'))
            ->groupBy('alumno_id', 'periodo_id', 'especialidad_id', 'maestro_id')
            ->havingRaw('count(*) > 1')
            ->get();

        $eliminados = 0;
        foreach ($grupos as $g) {
            $talleres = AlumnoTaller::where('estado', 'activo')
                ->where('alumno_id', $g->alumno_id)->where('periodo_id', $g->periodo_id)
                ->where('especialidad_id', $g->especialidad_id)->where('maestro_id', $g->maestro_id)
                ->withCount(['clases as marcadas_count' => fn ($q) => $q->whereHas('asistencia')])
                ->orderByDesc('marcadas_count')->orderBy('id')
                ->get();

            $queda = $talleres->shift();
            DB::transaction(function () use ($queda, $talleres, &$eliminados) {
                foreach ($talleres as $repetido) {
                    self::unir($queda, $repetido);
                    $eliminados++;
                }
            });
        }

        return $eliminados;
    }

    private static function unir(AlumnoTaller $queda, AlumnoTaller $repetido): void
    {
        $horariosQueda = $queda->horarios()->get()->keyBy('dia_semana');

        // Clases: las marcadas pasan al taller que queda (reemplazan a la suya
        // sin marcar del mismo dia); las demas se quitan.
        foreach ($repetido->clases()->with('asistencia')->get() as $clase) {
            if (! $clase->asistencia && $clase->estado !== 'realizada') {
                $clase->delete();
                continue;
            }

            $mismaFecha = Clase::where('alumno_taller_id', $queda->id)->whereDate('fecha', $clase->fecha)->with('asistencia')->first();
            if ($mismaFecha && ($mismaFecha->asistencia || $mismaFecha->estado === 'realizada')) {
                $clase->delete();
                continue;
            }
            $mismaFecha?->delete();

            $clase->update([
                'alumno_taller_id' => $queda->id,
                'horario_id' => $horariosQueda->get($clase->fecha->isoWeekday())?->id ?? $clase->horario_id,
            ]);
        }

        // Pagos: los abonos pasan a la mensualidad del taller que queda.
        foreach (Pago::where('alumno_taller_id', $repetido->id)->with('abonos')->get() as $pago) {
            $destino = Pago::where('alumno_taller_id', $queda->id)->where('mes', $pago->mes)->where('anio', $pago->anio)->first();

            if (! $destino) {
                $pago->update(['alumno_taller_id' => $queda->id]);
                continue;
            }

            foreach ($pago->abonos as $abono) {
                $abono->update(['pago_id' => $destino->id]);
            }
            $pago->delete();
            $destino->recalcular();
        }

        // Horarios repetidos (sus clases ya se movieron o quitaron).
        $repetido->horarios()->get()->each(function ($h) {
            Clase::where('horario_id', $h->id)->update(['horario_id' => null]);
            $h->deleteQuietly();
        });

        $repetido->delete();
    }
}
