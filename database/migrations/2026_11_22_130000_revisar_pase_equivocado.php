<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Solo lectura: que creo/cambio el pase de la madrugada del 22/11 (escribe en el log). */
return new class extends Migration
{
    public function up(): void
    {
        $desde = '2026-11-22 01:50:00';
        $hasta = '2026-11-22 02:05:00';
        $ap = DB::table('alumno_periodo')->whereBetween('updated_at', [$desde, $hasta])->get();
        Log::info('PASE-MAL alumno_periodo tocados: '.$ap->count().' por periodo/estado/nuevo: '.json_encode(
            $ap->groupBy(fn ($r) => $r->periodo_id.'|'.$r->estado.'|'.($r->created_at >= $desde ? 'nuevo' : 'existente'))->map->count()));
        Log::info('PASE-MAL inactivos marcados: '.json_encode($ap->where('estado', 'inactivo')->pluck('alumno_id')->values()));

        $t = DB::table('alumno_talleres')->whereBetween('created_at', [$desde, $hasta])->get(['id', 'alumno_id', 'periodo_id', 'especialidad_id', 'estado']);
        Log::info('PASE-MAL talleres creados: '.$t->count().' por periodo: '.json_encode($t->groupBy('periodo_id')->map->count()).' '.json_encode($t->take(40)));
        $ids = $t->pluck('id');
        $h = DB::table('horarios')->whereIn('alumno_taller_id', $ids)->count();
        $c = DB::table('clases')->whereIn('alumno_taller_id', $ids);
        $conAsis = DB::table('clases')->whereIn('alumno_taller_id', $ids)->whereExists(fn ($q) => $q->from('asistencias')->whereColumn('asistencias.clase_id', 'clases.id'))->count();
        $p = DB::table('pagos')->whereIn('alumno_taller_id', $ids)->get(['id', 'mes', 'anio', 'estado', 'monto_total', 'saldo', 'deleted_at']);
        $ab = DB::table('pago_abonos')->whereIn('pago_id', $p->pluck('id'))->count();
        Log::info("PASE-MAL horarios={$h} clases={$c->count()} clases_con_asistencia={$conAsis} pagos={$p->count()} abonos={$ab} ".json_encode($p->groupBy('estado')->map->count()));

        $otrosPagos = DB::table('pagos')->whereBetween('created_at', [$desde, $hasta])->whereNotIn('id', $p->pluck('id'))->count();
        $clasesBorradas = DB::table('clases')->whereBetween('updated_at', [$desde, $hasta])->whereNotIn('alumno_taller_id', $ids)->count();
        $alumnosTocados = DB::table('alumnos')->whereBetween('updated_at', [$desde, $hasta])->count();
        Log::info("PASE-MAL otros pagos creados={$otrosPagos} otras clases cambiadas={$clasesBorradas} alumnos actualizados={$alumnosTocados}");
        Log::info('PASE-MAL candidatos nov activos: '.DB::table('alumno_periodo')->where('periodo_id', 11)->where('estado', 'activo')->count()
            .' / talleres nov activos alumnos: '.DB::table('alumno_talleres')->where('periodo_id', 11)->where('estado', 'activo')->distinct()->count('alumno_id'));
    }

    public function down(): void
    {
    }
};
