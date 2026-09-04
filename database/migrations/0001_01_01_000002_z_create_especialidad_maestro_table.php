<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla pivote: un maestro puede ensenar varias especialidades
        // (ej. JEANPIER dicta Piano y Bateria; MIRIAM dicta Flauta, Piano y Saxofon).
        Schema::create('especialidad_maestro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maestro_id')->constrained('maestros')->cascadeOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->cascadeOnDelete();
            // Cada maestro puede tener una tarifa distinta por hora segun el
            // instrumento (ej. Jeanpier cobra distinto en Piano que en Bateria).
            $table->decimal('tarifa_hora', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['maestro_id', 'especialidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidad_maestro');
    }
};
