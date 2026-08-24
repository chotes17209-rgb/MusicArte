<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada maestro puede tener una tarifa distinta por hora segun el
        // instrumento (ej. Jeanpier cobra distinto en Piano que en Bateria).
        Schema::table('especialidad_maestro', function (Blueprint $table) {
            $table->decimal('tarifa_hora', 8, 2)->default(0)->after('especialidad_id');
        });
    }

    public function down(): void
    {
        Schema::table('especialidad_maestro', function (Blueprint $table) {
            $table->dropColumn('tarifa_hora');
        });
    }
};
