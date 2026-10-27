<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Solo lectura: deja en el registro como quedo Danna Santos Maldonado. No cambia datos. */
return new class extends Migration
{
    public function up(): void
    {
        $a = DB::table('alumnos')->where('nombre', 'Danna Santos Maldonado')->first();
        if (! $a) {
            return;
        }
        foreach (DB::table('alumno_talleres')->where('alumno_id', $a->id)->orderBy('periodo_id')->get() as $t) {
            Log::info('REVISAR Danna '.json_encode([
                'taller' => $t->id, 'estado' => $t->estado, 'monto' => $t->monto_mensual,
                'periodo' => DB::table('periodos')->where('id', $t->periodo_id)->value('nombre'),
                'taller_nombre' => DB::table('especialidades')->where('id', $t->especialidad_id)->value('nombre'),
                'maestro' => DB::table('maestros')->where('id', $t->maestro_id)->value('nombre'),
                'horarios' => DB::table('horarios')->where('alumno_taller_id', $t->id)->where('activo', true)->get(['dia_semana', 'hora_inicio']),
                'clases' => DB::table('clases')->where('alumno_taller_id', $t->id)->leftJoin('asistencias', 'asistencias.clase_id', '=', 'clases.id')
                    ->orderBy('fecha')->get(['clases.fecha', 'clases.estado', 'asistencias.estado as a'])
                    ->map(fn ($c) => substr($c->fecha, 5, 5).':'.$c->estado.':'.($c->a ?? '-'))->implode(' '),
                'pagos' => DB::table('pagos')->where('alumno_taller_id', $t->id)->whereNull('deleted_at')->get(['mes', 'monto_total', 'saldo', 'estado']),
            ]));
        }
        Log::info('REVISAR Danna matricula '.json_encode(DB::table('pagos')->where('alumno_id', $a->id)->where('tipo', 'matricula')->get(['anio', 'monto_total', 'saldo'])));
    }

    public function down(): void
    {
    }
};
