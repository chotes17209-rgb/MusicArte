<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fecha desde la que el alumno empieza un taller (p. ej. se inscribio a
 * mitad de mes). Sus clases y su asistencia se generan desde ese dia.
 * Vacia = desde el inicio del periodo (como hasta ahora). No cambia datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->after('periodo_id');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->dropColumn('fecha_inicio');
        });
    }
};
