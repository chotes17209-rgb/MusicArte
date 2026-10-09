<?php

use App\Models\Clase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. La asistencia admite el estado "recupero" (faltó y recuperó la clase).
 * 2. Los dias en que se cruzan dos periodos del alumno (p. ej. octubre desde
 *    el 28/09) son del periodo nuevo: se quitan las clases sin marcar del
 *    periodo anterior en esos dias.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE asistencias DROP CONSTRAINT IF EXISTS asistencias_estado_check');
        }
        Schema::table('asistencias', function (Blueprint $table) {
            $table->string('estado', 20)->default('asistio')->change();
        });

        Clase::quitarCruceDePeriodos();
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
