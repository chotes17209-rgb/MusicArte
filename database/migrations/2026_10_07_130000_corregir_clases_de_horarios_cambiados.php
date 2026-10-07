<?php

use App\Models\Clase;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

/**
 * Antes, al cambiar el dia de un horario (o desactivarlo/eliminarlo), sus
 * clases ya generadas seguian en el calendario y en Asistencia. Se quitan
 * las clases pendientes (de hoy en adelante, sin asistencia) que ya no
 * corresponden a su horario, y se crean las que faltan de los horarios
 * activos de los periodos vigentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $hoy = now()->toDateString();

        Clase::with('horario')
            ->where('estado', 'programada')
            ->whereDate('fecha', '>=', $hoy)
            ->whereDoesntHave('asistencia')
            ->whereNotNull('horario_id')
            ->chunkById(500, function ($clases) {
                $sobran = $clases->filter(fn (Clase $c) => ! $c->horario
                    || ! $c->horario->activo
                    || Carbon::parse($c->fecha)->isoWeekday() != $c->horario->dia_semana);

                if ($sobran->isNotEmpty()) {
                    Clase::whereIn('id', $sobran->pluck('id'))->delete();
                }
            });

        Horario::where('activo', true)
            ->whereHas('periodo', fn ($q) => $q->where('activo', true)->whereDate('fecha_fin', '>=', $hoy))
            ->where(fn ($q) => $q->whereNull('alumno_taller_id')
                ->orWhereHas('alumnoTaller', fn ($t) => $t->where('estado', 'activo')))
            ->get()
            ->each(fn (Horario $h) => $h->generarClases(now()));
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
