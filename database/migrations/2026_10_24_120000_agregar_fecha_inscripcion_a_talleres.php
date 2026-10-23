<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dia en que el alumno se inscribio al taller (puede ser antes de que
 * empiece el periodo). Columna nueva y vacia: no cambia datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->date('fecha_inscripcion')->nullable()->after('periodo_id');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->dropColumn('fecha_inscripcion');
        });
    }
};
