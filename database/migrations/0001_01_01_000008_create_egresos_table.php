<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('detalle');
            $table->decimal('yape_bcp', 8, 2)->default(0);
            $table->decimal('plin_ibk', 8, 2)->default(0);
            $table->decimal('tarjeta', 8, 2)->default(0);
            $table->decimal('efectivo', 8, 2)->default(0);
            $table->decimal('total', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
