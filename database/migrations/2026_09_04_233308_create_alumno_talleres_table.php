<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un alumno puede tener varios talleres (Piano, Canto, etc.) al mismo
        // tiempo. Cada fila de esta tabla representa "el alumno X esta
        // inscrito en el taller Y", con su propio maestro, periodo, salon y
        // estado, independiente de los demas talleres del mismo alumno.
        Schema::create('alumno_talleres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->restrictOnDelete();
            $table->foreignId('maestro_id')->nullable()->constrained('maestros')->nullOnDelete();
            $table->foreignId('periodo_id')->nullable()->constrained('periodos')->nullOnDelete();
            $table->string('salon')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->index(['alumno_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_talleres');
    }
};