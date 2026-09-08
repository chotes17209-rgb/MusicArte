<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seccion 9 y 11 del requerimiento: cada pago (concepto de deuda) ahora
 * puede asociarse a un taller especifico del alumno, y tiene un "estado"
 * (pendiente / a_cuenta / pagado) que se recalcula automaticamente segun
 * cuanto se ha abonado (ver PagoAbono y Pago::recalcular()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('alumno_taller_id')->nullable()->after('alumno_id')
                ->constrained('alumno_talleres')->nullOnDelete();
            $table->string('estado', 20)->default('pendiente')->after('saldo');
        });

        // Recalcula el estado de los pagos ya existentes segun su saldo,
        // sin tocar ningun otro dato (no se pierde historial).
        DB::table('pagos')->where('saldo', '<=', 0)->update(['estado' => 'pagado']);
        DB::table('pagos')->where('saldo', '>', 0)
            ->whereRaw('(yape_transferencia + efectivo + tarjeta) > 0')
            ->update(['estado' => 'a_cuenta']);
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alumno_taller_id');
            $table->dropColumn('estado');
        });
    }
};
