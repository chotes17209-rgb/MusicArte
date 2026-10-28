<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Solo lectura: deja en el registro las clases repetidas (mismo alumno,
 * mismo dia, mismo taller) de los periodos recientes, con sus talleres y
 * horarios, para ver de donde salen. No cambia datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        $repetidas = DB::table('clases')
            ->whereDate('fecha', '>=', now()->subDays(40)->toDateString())
            ->select('alumno_id', 'especialidad_id', DB::raw('CAST(fecha AS DATE) as dia'), DB::raw('count(*) as n'))
            ->groupBy('alumno_id', 'especialidad_id', DB::raw('CAST(fecha AS DATE)'))
            ->havingRaw('count(*) > 1')->get();
        Log::info('REPETIDAS total grupos: '.$repetidas->count());

        foreach ($repetidas->groupBy('alumno_id') as $alumnoId => $grupos) {
            $clases = DB::table('clases')->where('clases.alumno_id', $alumnoId)
                ->whereIn(DB::raw('CAST(clases.fecha AS DATE)'), $grupos->pluck('dia'))
                ->leftJoin('asistencias', 'asistencias.clase_id', '=', 'clases.id')
                ->orderBy('clases.fecha')->get(['clases.id', 'clases.fecha', 'clases.hora_inicio', 'clases.estado', 'clases.alumno_taller_id', 'clases.horario_id', 'clases.periodo_id', 'asistencias.estado as a']);
            $talleres = DB::table('alumno_talleres')->whereIn('id', $clases->pluck('alumno_taller_id')->filter()->unique())
                ->get(['id', 'periodo_id', 'especialidad_id', 'maestro_id', 'estado', 'created_at']);
            $horarios = DB::table('horarios')->whereIn('id', $clases->pluck('horario_id')->filter()->unique())
                ->get(['id', 'alumno_taller_id', 'periodo_id', 'dia_semana', 'hora_inicio', 'activo']);
            Log::info('REPETIDAS '.json_encode([
                'alumno' => DB::table('alumnos')->where('id', $alumnoId)->value('nombre'),
                'dias' => $grupos->pluck('dia')->map(fn ($d) => substr($d, 0, 10))->all(),
                'clases' => $clases->map(fn ($c) => $c->id.':'.substr($c->fecha, 0, 10).' '.substr($c->hora_inicio, 0, 5).':'.$c->estado.':'.($c->a ?? '-').':t'.$c->alumno_taller_id.':h'.$c->horario_id.':p'.$c->periodo_id)->all(),
                'talleres' => $talleres, 'horarios' => $horarios,
            ]));
        }
    }

    public function down(): void
    {
    }
};
