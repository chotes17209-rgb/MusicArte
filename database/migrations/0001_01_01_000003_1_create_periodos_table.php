<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aqui se define cuanto dura cada periodo (normalmente 4 semanas).
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Agosto 2026"
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};