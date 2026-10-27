<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Solo lectura: deja en el registro del servidor el detalle de los talleres
 * dados de baja de los periodos recientes (y de Danna Santos Maldonado),
 * para revisar por que no aparecen en la asistencia del mes. No cambia datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        $periodos = DB::table('periodos')->whereDate('fecha_fin', '>=', now()->subDays(60)->toDateString())->pluck('nombre', 'id');

        $alumnos = DB::table('alumnos')->where('nombre', 'like', '%Danna%Santos%')
            ->orWhere('nombre', 'like', '%Santos Maldonado%')->get(['id', 'nombre', 'activo']);
        Log::info('REVISION alumno buscado: '.json_encode($alumnos));

        $talleres = DB::table('alumno_talleres')
            ->where(fn ($q) => $q->whereIn('alumno_id', $alumnos->pluck('id'))
                ->orWhere(fn ($q) => $q->whereIn('periodo_id', $periodos->keys())->where('estado', 'inactivo')))
            ->get(['id', 'alumno_id', 'periodo_id', 'especialidad_id', 'estado', 'updated_at']);

        foreach ($talleres as $t) {
            $clases = DB::table('clases')->where('alumno_taller_id', $t->id)
                ->leftJoin('asistencias', 'asistencias.clase_id', '=', 'clases.id')
                ->get(['clases.fecha', 'clases.estado', 'clases.periodo_id', 'clases.updated_at', 'asistencias.estado as asistencia']);
            Log::info('REVISION taller '.json_encode([
                'taller' => $t,
                'alumno' => DB::table('alumnos')->where('id', $t->alumno_id)->value('nombre'),
                'periodo' => $periodos[$t->periodo_id] ?? $t->periodo_id,
                'clases' => $clases->map(fn ($c) => substr($c->fecha, 0, 10).':'.$c->estado.':'.($c->asistencia ?? '-').':p'.$c->periodo_id.':'.substr((string) $c->updated_at, 0, 16))->all(),
                'pagos' => DB::table('pagos')->where('alumno_taller_id', $t->id)->get(['mes', 'anio', 'monto_total', 'saldo', 'deleted_at']),
            ]));
        }
    }

    public function down(): void
    {
    }
};
