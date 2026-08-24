<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->foreignId('periodo_id')->nullable()->after('alumno_id')->constrained('periodos')->nullOnDelete();
        });
        Schema::table('clases', function (Blueprint $table) {
            $table->foreignId('periodo_id')->nullable()->after('horario_id')->constrained('periodos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_id');
        });
        Schema::table('clases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodo_id');
        });
    }
};