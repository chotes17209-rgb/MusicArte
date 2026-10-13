<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Al cambiar la hora de un horario, las clases ya marcadas se quedaban con
 * la hora anterior (p. ej. salia a las 6 pm un alumno que va de 4 a 5 pm).
 * En los periodos recientes, cada clase toma la hora de su horario activo.
 * La asistencia marcada no cambia.
 */
return new class extends Migration
{
    public function up(): void
    {
        $horarios = DB::table('horarios')
            ->join('periodos', 'periodos.id', '=', 'horarios.periodo_id')
            ->where('horarios.activo', true)
            ->whereDate('periodos.fecha_fin', '>=', now()->subDays(45)->toDateString())
            ->select('horarios.id', 'horarios.hora_inicio', 'horarios.hora_fin')
            ->get();

        foreach ($horarios as $h) {
            DB::table('clases')
                ->where('horario_id', $h->id)
                ->where(fn ($q) => $q->where('hora_inicio', '!=', $h->hora_inicio)->orWhere('hora_fin', '!=', $h->hora_fin))
                ->update(['hora_inicio' => $h->hora_inicio, 'hora_fin' => $h->hora_fin]);
        }
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
