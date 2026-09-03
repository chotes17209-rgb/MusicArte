<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FASE 1 - Requerimientos 2.1, 2.2, 3 y 4:
     *  - La edad ya no se guarda a mano: se calcula desde fecha_nacimiento (se quita la columna).
     *  - Se quita "direccion" del alumno (ya no se usa en ningun formulario).
     *  - Un alumno puede tener VARIOS talleres: cada fila de "horarios" ahora es
     *    un taller independiente (especialidad + maestro + periodo + dia + hora),
     *    en vez de heredar siempre el maestro/especialidad "unico" del alumno.
     *    Para eso agregamos un indice compuesto que evita duplicar el mismo
     *    taller+dia dos veces, pero SI permite que el mismo alumno tenga, por
     *    ejemplo, Piano el lunes y Canto el lunes al mismo tiempo (son filas
     *    distintas porque especialidad_id/maestro_id son distintos).
     */
    public function up(): void
    {
        // 1) Alumnos: eliminar campos innecesarios (no se pierde el historial de
        //    actividad del alumno, solo estos dos campos que ya no se solicitan).
        Schema::table('alumnos', function (Blueprint $table) {
            if (Schema::hasColumn('alumnos', 'direccion')) {
                $table->dropColumn('direccion');
            }
            if (Schema::hasColumn('alumnos', 'edad')) {
                $table->dropColumn('edad');
            }
        });

        // 2) Horarios: agregar "estado" propio del taller (independiente del
        //    campo "activo" que ya existia, para dejar el texto explicito:
        //    activo / pausado / finalizado) y el indice compuesto.
        Schema::table('horarios', function (Blueprint $table) {
            if (!Schema::hasColumn('horarios', 'estado')) {
                $table->string('estado', 20)->default('activo')->after('activo');
            }
            $table->index(
                ['alumno_id', 'especialidad_id', 'maestro_id', 'periodo_id', 'dia_semana'],
                'horarios_alumno_taller_dia_idx'
            );
        });

        // Sincronizamos el nuevo campo "estado" con el "activo" existente para
        // no perder informacion de los horarios que ya estaban creados.
        DB::table('horarios')->where('activo', true)->update(['estado' => 'activo']);
        DB::table('horarios')->where('activo', false)->update(['estado' => 'pausado']);
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropIndex('horarios_alumno_taller_dia_idx');
            if (Schema::hasColumn('horarios', 'estado')) {
                $table->dropColumn('estado');
            }
        });

        Schema::table('alumnos', function (Blueprint $table) {
            if (!Schema::hasColumn('alumnos', 'direccion')) {
                $table->string('direccion')->nullable();
            }
            if (!Schema::hasColumn('alumnos', 'edad')) {
                $table->string('edad')->nullable();
            }
        });
    }
};
