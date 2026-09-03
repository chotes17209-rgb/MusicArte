<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FASE 2 - Requerimientos 9, 10, 11, 12, 13, 14:
     *  - Cada Pago pasa a ser el "concepto/deuda" (alumno + taller + periodo +
     *    monto total). El detalle de como se fue pagando vive en la nueva
     *    tabla pago_abonos: un abono = un movimiento real de dinero, con su
     *    propio monto, fecha, metodo de pago y numero de recibo.
     *  - El saldo y el estado del Pago se recalculan automaticamente cada vez
     *    que se agrega/edita/borra un abono (ver App\Models\PagoAbono).
     *  - No se pierde el historial: las columnas viejas (yape_transferencia,
     *    efectivo, tarjeta) NO se borran, y se migran a abonos automaticos
     *    para que el historial anterior tambien aparezca en el detalle.
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('periodo_id')->nullable()->after('alumno_id')->constrained('periodos')->nullOnDelete();
            $table->foreignId('especialidad_id')->nullable()->after('periodo_id')->constrained('especialidades')->nullOnDelete();
            $table->foreignId('maestro_id')->nullable()->after('especialidad_id')->constrained('maestros')->nullOnDelete();
            $table->decimal('monto_pagado', 8, 2)->default(0)->after('monto_total');
            $table->string('estado', 20)->default('pendiente')->after('saldo');
            $table->foreignId('usuario_id')->nullable()->after('estado')->constrained('users')->nullOnDelete();
        });

        Schema::create('pago_abonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
            $table->decimal('monto', 8, 2);
            $table->date('fecha');
            $table->string('metodo_pago', 30);
            $table->string('recibo_nro')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('fecha');
        });

        // --- Backfill: no perder historial existente ---

        // 1) Asociar cada pago viejo a su Periodo, especialidad y maestro
        //    (via el alumno), usando mes/anio como referencia.
        $pagos = DB::table('pagos')->get();
        foreach ($pagos as $pago) {
            $periodo = DB::table('periodos')->where('mes', $pago->mes)->where('anio', $pago->anio)->first();
            $alumno = DB::table('alumnos')->where('id', $pago->alumno_id)->first();

            DB::table('pagos')->where('id', $pago->id)->update([
                'periodo_id' => $periodo->id ?? null,
                'especialidad_id' => $alumno->especialidad_id ?? null,
                'maestro_id' => $alumno->maestro_id ?? null,
            ]);

            // 2) Convertir los montos por metodo ya guardados en abonos reales,
            //    para que el modulo de abonos ya tenga el historial completo.
            $fecha = $pago->fecha_pago ?? now()->toDateString();
            $montos = [
                'transferencia' => (float) $pago->yape_transferencia,
                'efectivo' => (float) $pago->efectivo,
                'tarjeta' => (float) $pago->tarjeta,
            ];
            foreach ($montos as $metodo => $monto) {
                if ($monto > 0) {
                    DB::table('pago_abonos')->insert([
                        'pago_id' => $pago->id,
                        'monto' => $monto,
                        'fecha' => $fecha,
                        'metodo_pago' => $metodo,
                        'recibo_nro' => $pago->recibo_nro,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $pagado = $montos['transferencia'] + $montos['efectivo'] + $montos['tarjeta'];
            $estado = $pagado <= 0 ? 'pendiente' : ($pagado >= $pago->monto_total ? 'pagado' : 'a_cuenta');

            DB::table('pagos')->where('id', $pago->id)->update([
                'monto_pagado' => $pagado,
                'estado' => $estado,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_abonos');

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_id');
            $table->dropConstrainedForeignId('especialidad_id');
            $table->dropConstrainedForeignId('maestro_id');
            $table->dropConstrainedForeignId('usuario_id');
            $table->dropColumn(['monto_pagado', 'estado']);
        });
    }
};
