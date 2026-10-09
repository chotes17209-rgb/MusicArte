<?php

use App\Models\Clase;
use Illuminate\Database\Migrations\Migration;

/**
 * "Generar clases" usaba los horarios de todos los meses: un horario de
 * setiembre creaba clases en octubre en dias que el alumno ya no tenia.
 * Se quitan las clases que caen fuera del periodo de su horario y que aun
 * no se dictaron ni tienen asistencia (lo ya dictado se conserva).
 */
return new class extends Migration
{
    public function up(): void
    {
        Clase::with('horario.periodo')
            ->where('estado', 'programada')
            ->whereNotNull('horario_id')
            ->whereDoesntHave('asistencia')
            ->chunkById(500, function ($clases) {
                $fuera = $clases->filter(function (Clase $c) {
                    $periodo = $c->horario?->periodo;

                    return $periodo && ($c->fecha->lt($periodo->fecha_inicio) || $c->fecha->gt($periodo->fecha_fin));
                });

                if ($fuera->isNotEmpty()) {
                    Clase::whereIn('id', $fuera->pluck('id'))->delete();
                }
            });
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
