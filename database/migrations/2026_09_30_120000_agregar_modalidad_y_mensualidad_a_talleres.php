<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cada taller inscrito guarda su modalidad (veces por semana) y la
 * mensualidad que paga el alumno, escrita a mano al inscribirlo.
 *
 * Los talleres existentes se completan con lo ya registrado: la modalidad
 * sale de los dias de su horario y la mensualidad de su pago del mes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->unsignedTinyInteger('veces_semana')->nullable()->after('salon');
            $table->decimal('monto_mensual', 8, 2)->nullable()->after('veces_semana');
        });

        $this->completarTalleres();
    }

    public function down(): void
    {
        Schema::table('alumno_talleres', function (Blueprint $table) {
            $table->dropColumn(['veces_semana', 'monto_mensual']);
        });
    }

    private function completarTalleres(): void
    {
        $dias = DB::table('horarios')
            ->whereNotNull('alumno_taller_id')
            ->select('alumno_taller_id', DB::raw('count(distinct dia_semana) as dias'))
            ->groupBy('alumno_taller_id')
            ->pluck('dias', 'alumno_taller_id');

        // Monto del ultimo pago registrado de cada taller.
        $montos = DB::table('pagos')
            ->whereNotNull('alumno_taller_id')
            ->orderBy('anio')->orderBy('mes')->orderBy('id')
            ->get(['alumno_taller_id', 'monto_total'])
            ->pluck('monto_total', 'alumno_taller_id');

        DB::table('alumno_talleres')->orderBy('id')->select('id')->chunkById(500, function ($talleres) use ($dias, $montos) {
            foreach ($talleres as $t) {
                $veces = $dias[$t->id] ?? null;
                $monto = $montos[$t->id] ?? null;

                if ($veces === null && $monto === null) {
                    continue;
                }

                DB::table('alumno_talleres')->where('id', $t->id)->update([
                    'veces_semana' => $veces ? min((int) $veces, 7) : null,
                    'monto_mensual' => $monto,
                ]);
            }
        });
    }
};
