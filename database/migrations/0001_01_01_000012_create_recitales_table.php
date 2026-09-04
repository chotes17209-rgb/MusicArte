<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recitales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->date('fecha')->nullable();
            $table->string('tema')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('participantes')->nullable();
            $table->decimal('pago_por_alumno', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recitales');
    }
};
