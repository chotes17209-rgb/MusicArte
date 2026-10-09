<?php

use App\Models\Clase;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

/**
 * Las clases de un horario cambiado solo se movian de hoy en adelante; las
 * de dias pasados del mes (que se marcan despues) seguian en el dia viejo.
 * Se quitan las clases sin asistencia que ya no coinciden con su horario
 * (dia distinto o horario desactivado) y se crean las que faltan en los
 * periodos recientes (el actual y el anterior).
 */
return new class extends Migration
{
    public function up(): void
    {
        Clase::with('horario')
            ->where('estado', 'programada')
            ->whereNotNull('horario_id')
            ->whereDoesntHave('asistencia')
            ->chunkById(500, function ($clases) {
                $sobran = $clases->filter(fn (Clase $c) => ! $c->horario
                    || ! $c->horario->activo
                    || $c->fecha->isoWeekday() != $c->horario->dia_semana);

                if ($sobran->isNotEmpty()) {
                    Clase::whereIn('id', $sobran->pluck('id'))->delete();
                }
            });

        Horario::where('activo', true)
            ->whereHas('periodo', fn ($q) => $q->whereDate('fecha_fin', '>=', Carbon::now()->subDays(45)->toDateString()))
            ->where(fn ($q) => $q->whereNull('alumno_taller_id')
                ->orWhereHas('alumnoTaller', fn ($t) => $t->where('estado', 'activo')))
            ->get()
            ->each(fn (Horario $h) => $h->generarClases());
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
