<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Corrige bases de datos donde la tabla alumnos se creo antes de
        // que existiera la columna 'edad' (por eso fallaba con
        // "Unknown column 'edad' in field list").
        if (! Schema::hasColumn('alumnos', 'edad')) {
            Schema::table('alumnos', function (Blueprint $table) {
                $table->string('edad')->nullable()->after('fecha_nacimiento');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('alumnos', 'edad')) {
            Schema::table('alumnos', function (Blueprint $table) {
                $table->dropColumn('edad');
            });
        }
    }
};