<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Fecha de nacimiento del maestro, para el recordatorio de cumpleaños del inicio. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('maestros', 'fecha_nacimiento')) {
            Schema::table('maestros', fn (Blueprint $t) => $t->date('fecha_nacimiento')->nullable()->after('email'));
        }
    }

    public function down(): void
    {
        Schema::table('maestros', fn (Blueprint $t) => $t->dropColumn('fecha_nacimiento'));
    }
};
