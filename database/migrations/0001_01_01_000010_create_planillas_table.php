<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pago mensual a maestros por horas dictadas. Tambien restringido a admin.
        Schema::create('planillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maestro_id')->constrained('maestros')->cascadeOnDelete();
            $table->unsignedTinyInteger('mes');
            $table->smallInteger('anio');
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->nullOnDelete();
            $table->decimal('horas', 6, 1)->default(0);
            $table->decimal('monto', 8, 2)->default(0);
            $table->string('observacion')->nullable();
            $table->timestamps();

            $table->index(['mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planillas');
    }
};
