<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fecha en que el periodo se cerro solo al vencer su fecha de fin. Sirve
 * para cerrarlo una unica vez: si luego el administrador lo reabre a mano,
 * no se vuelve a cerrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodos', function (Blueprint $table) {
            $table->timestamp('cerrado_en')->nullable()->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('periodos', function (Blueprint $table) {
            $table->dropColumn('cerrado_en');
        });
    }
};
