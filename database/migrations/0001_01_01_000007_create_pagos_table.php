<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modulo sensible: solo el rol admin puede crear/editar montos (ver EnsureRole + PagoController).
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->unsignedTinyInteger('mes');
            $table->smallInteger('anio');
            $table->string('concepto')->nullable();
            $table->decimal('monto_total', 8, 2)->default(0);
            $table->decimal('yape_transferencia', 8, 2)->default(0);
            $table->decimal('efectivo', 8, 2)->default(0);
            $table->decimal('tarjeta', 8, 2)->default(0);
            $table->decimal('saldo', 8, 2)->default(0);
            $table->string('recibo_nro')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
