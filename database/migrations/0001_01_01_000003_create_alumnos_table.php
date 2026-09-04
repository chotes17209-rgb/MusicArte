<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('edad')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->nullOnDelete();
            $table->foreignId('maestro_id')->nullable()->constrained('maestros')->nullOnDelete();
            $table->string('tutor')->nullable();
            $table->string('celular')->nullable();
            $table->string('dni')->nullable();
            $table->text('diagnostico')->nullable();
            $table->string('direccion')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
