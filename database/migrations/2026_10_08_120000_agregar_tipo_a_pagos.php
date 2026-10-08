<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipo de cobro: la mensualidad de un taller (cada mes) o la matricula
 * (una vez por año por alumno, sin taller).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('tipo', 20)->default('mensualidad')->after('alumno_taller_id');
            $table->index(['alumno_id', 'tipo', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['alumno_id', 'tipo', 'anio']);
            $table->dropColumn('tipo');
        });
    }
};
