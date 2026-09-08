<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->foreignId('alumno_taller_id')->nullable()->after('alumno_id')
                ->constrained('alumno_talleres')->cascadeOnDelete();
        });

        Schema::table('clases', function (Blueprint $table) {
            $table->foreignId('alumno_taller_id')->nullable()->after('alumno_id')
                ->constrained('alumno_talleres')->nullOnDelete();
        });

        // ---------------------------------------------------------------
        // Backfill: hasta ahora un alumno solo podia tener UNA especialidad
        // y UN maestro (columnas alumnos.especialidad_id / maestro_id).
        // Convertimos ese dato existente en su "primer taller" dentro de
        // alumno_talleres, y enlazamos sus horarios/clases ya creados a ese
        // taller. No se borra ni se pierde ningun registro.
        // ---------------------------------------------------------------
        $alumnos = DB::table('alumnos')->whereNotNull('especialidad_id')->get();

        foreach ($alumnos as $alumno) {
            $periodoDelAlumno = DB::table('horarios')
                ->where('alumno_id', $alumno->id)
                ->value('periodo_id');

            $alumnoTallerId = DB::table('alumno_talleres')->insertGetId([
                'alumno_id' => $alumno->id,
                'especialidad_id' => $alumno->especialidad_id,
                'maestro_id' => $alumno->maestro_id,
                'periodo_id' => $periodoDelAlumno,
                'estado' => $alumno->activo ? 'activo' : 'inactivo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('horarios')->where('alumno_id', $alumno->id)->update([
                'alumno_taller_id' => $alumnoTallerId,
            ]);

            DB::table('clases')->where('alumno_id', $alumno->id)->update([
                'alumno_taller_id' => $alumnoTallerId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alumno_taller_id');
        });

        Schema::table('clases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alumno_taller_id');
        });
    }
};