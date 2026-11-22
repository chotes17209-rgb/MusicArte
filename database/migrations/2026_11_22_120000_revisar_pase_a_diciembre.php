<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Solo lectura: revisa el estado de los periodos y el pase a diciembre (escribe en el log). */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('periodos')->orderBy('anio')->orderBy('mes')->get() as $p) {
            if ($p->anio < 2026 || $p->mes < 9) {
                continue;
            }
            $ap = DB::table('alumno_periodo')->where('periodo_id', $p->id)->selectRaw('estado, count(*) n')->groupBy('estado')->pluck('n', 'estado');
            $at = DB::table('alumno_talleres')->where('periodo_id', $p->id)->selectRaw('estado, count(*) n')->groupBy('estado')->pluck('n', 'estado');
            $h = DB::table('horarios')->where('periodo_id', $p->id)->selectRaw('activo, count(*) n')->groupBy('activo')->pluck('n', 'activo');
            $c = DB::table('clases')->where('periodo_id', $p->id)->count();
            Log::info("PASE-DIC periodo {$p->id} {$p->nombre} activo=".json_encode($p->activo)." {$p->fecha_inicio}..{$p->fecha_fin} cerrado={$p->cerrado_en} creado={$p->created_at}"
                .' alumno_periodo='.json_encode($ap).' talleres='.json_encode($at).' horarios='.json_encode($h)." clases={$c}");
        }
        $ultimos = DB::table('alumno_periodo')->orderByDesc('updated_at')->limit(5)->get(['alumno_id', 'periodo_id', 'estado', 'updated_at']);
        Log::info('PASE-DIC ultimos alumno_periodo: '.json_encode($ultimos));
        Log::info('PASE-DIC alumnos activos: '.DB::table('alumnos')->where('activo', true)->count().' de '.DB::table('alumnos')->count());
    }

    public function down(): void
    {
    }
};
