<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_chica', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('proveedor')->nullable();
            $table->string('descripcion');
            $table->decimal('monto', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_chica');
    }
};
