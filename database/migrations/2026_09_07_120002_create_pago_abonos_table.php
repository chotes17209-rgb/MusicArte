<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seccion 10 del requerimiento: un pago (la deuda/concepto, tabla pagos)
 * puede tener VARIOS abonos. Cada abono es un movimiento de dinero
 * independiente: tiene su propia fecha, metodo de pago y numero de
 * recibo, y todos quedan relacionados al pago original.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_abonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
            $table->decimal('monto', 8, 2);
            $table->date('fecha');
            $table->string('metodo_pago', 20); // transferencia | yape | efectivo
            $table->string('recibo_nro')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index('pago_id');
        });

        // Migra los pagos ya existentes: cada monto por metodo (yape,
        // efectivo, tarjeta) que ya tenian registrado se convierte en su
        // propio abono historico, para no perder informacion (regla 4).
        $pagos = DB::table('pagos')->get();

        foreach ($pagos as $pago) {
            $fecha = $pago->fecha_pago ?? $pago->created_at ?? now()->toDateString();
            $metodos = [
                'transferencia' => (float) $pago->yape_transferencia,
                'efectivo' => (float) $pago->efectivo,
                'tarjeta' => (float) $pago->tarjeta,
            ];

            $primero = true;
            foreach ($metodos as $metodo => $monto) {
                if ($monto <= 0) {
                    continue;
                }

                DB::table('pago_abonos')->insert([
                    'pago_id' => $pago->id,
                    'monto' => $monto,
                    'fecha' => $fecha,
                    'metodo_pago' => $metodo === 'tarjeta' ? 'transferencia' : $metodo,
                    'recibo_nro' => $primero ? $pago->recibo_nro : null,
                    'observacion' => $primero ? null : 'Abono historico migrado automaticamente.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $primero = false;
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_abonos');
    }
};
