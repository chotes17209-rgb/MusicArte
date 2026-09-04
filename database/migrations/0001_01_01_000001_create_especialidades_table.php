<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('color', 7)->default('#3d2c8d'); // color para el calendario
            $table->decimal('precio_mensual', 8, 2)->default(0); // dato sensible: solo admin lo edita
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};
