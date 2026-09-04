<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La planilla real es por alumno (cada fila = un estudiante que tomo
        // clases con ese maestro ese mes), no un solo monto agregado por maestro.
        Schema::table('planillas', function (Blueprint $table) {
            $table->foreignId('alumno_id')->nullable()->after('maestro_id')
                ->constrained('alumnos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('planillas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alumno_id');
        });
    }
};
