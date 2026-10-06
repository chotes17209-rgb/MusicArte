<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nota libre sobre la mensualidad del taller, por ejemplo: "Entró a mitad
 * de mes, paga solo 4 clases". Tambien se copia a la observacion del pago.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->string('nota_mensualidad', 255)->nullable()->after('monto_mensual');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->dropColumn('nota_mensualidad');
        });
    }
};
