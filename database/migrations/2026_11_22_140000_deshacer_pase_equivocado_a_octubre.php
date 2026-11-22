<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Deshace el pase de alumnos del 22/11 (1:56 a. m.) que por error se hizo
 * hacia Octubre en vez de Diciembre: quita los talleres, horarios, clases y
 * mensualidades que ese pase creo en octubre (sin asistencias ni abonos) y
 * devuelve el historial de octubre como estaba. No toca nada mas.
 */
return new class extends Migration
{
    private const DESDE = '2026-11-22 01:50:00';
    private const HASTA = '2026-11-22 02:05:00';

    public function up(): void
    {
        $talleres = DB::table('alumno_talleres')->whereBetween('created_at', [self::DESDE, self::HASTA])
            ->whereBetween('updated_at', [self::DESDE, self::HASTA])->get(['id', 'periodo_id']);
        $periodos = $talleres->pluck('periodo_id')->unique();
        if ($talleres->isEmpty() || $talleres->count() > 40 || $periodos->count() !== 1) {
            Log::info('DESHACER-PASE: no se encontro el pase esperado ('.$talleres->count().' talleres), no se hizo nada.');

            return;
        }
        $periodoId = $periodos->first();

        DB::transaction(function () use ($talleres, $periodoId) {
            $quitados = 0;
            $omitidos = [];
            foreach ($talleres->pluck('id') as $id) {
                $clases = DB::table('clases')->where('alumno_taller_id', $id)->pluck('id');
                $pagos = DB::table('pagos')->where('alumno_taller_id', $id)->pluck('id');
                $conAsistencia = DB::table('asistencias')->whereIn('clase_id', $clases)->exists();
                $conAbonos = DB::table('pago_abonos')->whereIn('pago_id', $pagos)->exists();
                if ($conAsistencia || $conAbonos) {
                    $omitidos[] = $id;

                    continue;
                }
                DB::table('clases')->whereIn('id', $clases)->delete();
                DB::table('horarios')->where('alumno_taller_id', $id)->delete();
                DB::table('pagos')->whereIn('id', $pagos)->delete();
                DB::table('alumno_talleres')->where('id', $id)->delete();
                $quitados++;
            }

            // Historial de octubre: las filas nuevas se quitan y las cambiadas vuelven a su estado.
            $tocadas = DB::table('alumno_periodo')->where('periodo_id', $periodoId)
                ->whereBetween('updated_at', [self::DESDE, self::HASTA])->get();
            $nuevas = $tocadas->filter(fn ($r) => $r->created_at >= self::DESDE)->pluck('id');
            $cambiadas = $tocadas->filter(fn ($r) => $r->created_at < self::DESDE);
            DB::table('alumno_periodo')->whereIn('id', $nuevas)->delete();
            foreach ($cambiadas as $r) {
                DB::table('alumno_periodo')->where('id', $r->id)
                    ->update(['estado' => $r->estado === 'activo' ? 'inactivo' : 'activo']);
            }

            Log::info("DESHACER-PASE: periodo {$periodoId}: talleres quitados={$quitados} omitidos=".json_encode($omitidos)
                ." historial: quitadas={$nuevas->count()} restauradas={$cambiadas->count()}");
        });
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
