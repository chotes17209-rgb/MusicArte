<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta es la fuente de verdad del historial: "el alumno X estuvo
        // activo/inactivo en el periodo Y". Es independiente de que tenga o
        // no un taller con horario armado ese mes (seccion 6 y 7).
        Schema::create('alumno_periodo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos')->cascadeOnDelete();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->unique(['alumno_id', 'periodo_id']);
        });

        // ---------------------------------------------------------------
        // Backfill: los talleres que ya existen (con periodo asignado) se
        // usan para reconstruir el historial. Si un alumno tuvo, en un
        // mismo periodo, al menos un taller activo, queda "activo" ese mes.
        // ---------------------------------------------------------------
        $filas = DB::table('alumno_talleres')->whereNotNull('periodo_id')->get(['alumno_id', 'periodo_id', 'estado']);

        $agrupado = [];
        foreach ($filas as $f) {
            $clave = $f->alumno_id.'-'.$f->periodo_id;
            if (! isset($agrupado[$clave]) || $f->estado === 'activo') {
                $agrupado[$clave] = ['alumno_id' => $f->alumno_id, 'periodo_id' => $f->periodo_id, 'estado' => $f->estado];
            }
        }

        foreach ($agrupado as $fila) {
            DB::table('alumno_periodo')->updateOrInsert(
                ['alumno_id' => $fila['alumno_id'], 'periodo_id' => $fila['periodo_id']],
                ['estado' => $fila['estado'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_periodo');
    }
};
