<?php

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Pago;
use App\Models\PagoAbono;
use App\Models\Periodo;
use App\Services\HorarioService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trae de la copia de la base de datos (recuperacion de Render, variable
 * DB_RESPALDO_URL) lo que Danna Santos Maldonado tenia en octubre: su
 * taller (horario y mensualidad), su asistencia, su pago y su matricula
 * 2026. Solo toca los registros de ella; si la copia no esta configurada o
 * ella no aparece ahi, no hace nada.
 */
return new class extends Migration
{
    private const NOMBRE = 'Danna Santos Maldonado';

    public function up(): void
    {
        $url = env('DB_RESPALDO_URL');
        if (! $url) {
            Log::info('RESPALDO: no hay copia configurada, no se hizo nada.');

            return;
        }
        config(['database.connections.respaldo' => array_merge(config('database.connections.pgsql'), ['url' => $url])]);
        $r = DB::connection('respaldo');

        $alB = $r->table('alumnos')->where('nombre', self::NOMBRE)->first();
        $al = Alumno::where('nombre', self::NOMBRE)->first();
        if (! $alB || ! $al) {
            Log::info('RESPALDO: '.(! $alB ? 'la alumna no esta en la copia' : 'la alumna no esta en la base actual').', no se hizo nada.');

            return;
        }

        $octB = $r->table('periodos')->where('anio', 2026)->where('mes', 10)->first();
        $oct = Periodo::where('anio', 2026)->where('mes', 10)->first();
        $talleresB = $octB ? $r->table('alumno_talleres')->where('alumno_id', $alB->id)->where('periodo_id', $octB->id)->get() : collect();
        Log::info('RESPALDO: en la copia tiene '.$talleresB->count().' taller(es) de octubre: '.json_encode($talleresB));

        DB::transaction(function () use ($r, $alB, $al, $oct, $talleresB) {
            $resumen = ['asistencias' => 0, 'abonos' => 0, 'pagos' => 0];

            foreach ($talleresB as $tb) {
                if (! $oct) {
                    break;
                }
                // El taller de octubre en la base actual (mismo taller y maestro), o se crea.
                $t = AlumnoTaller::where('alumno_id', $al->id)->where('periodo_id', $oct->id)
                    ->where('especialidad_id', $tb->especialidad_id)->where('maestro_id', $tb->maestro_id)->first()
                    ?? AlumnoTaller::create(['alumno_id' => $al->id, 'especialidad_id' => $tb->especialidad_id,
                        'maestro_id' => $tb->maestro_id, 'periodo_id' => $oct->id, 'estado' => 'activo']);

                $t->update(array_filter([
                    'salon' => $tb->salon ?? null,
                    'veces_semana' => $tb->veces_semana ?? null,
                    'monto_mensual' => $tb->monto_mensual ?? null,
                    'nota_mensualidad' => $tb->nota_mensualidad ?? null,
                    'fecha_inicio' => $tb->fecha_inicio ?? null,
                    'fecha_inscripcion' => $tb->fecha_inscripcion ?? null,
                ], fn ($v) => $v !== null));

                // Mismo horario que tenia.
                $horariosB = $r->table('horarios')->where('alumno_taller_id', $tb->id)->where('activo', true)->get();
                if ($horariosB->isNotEmpty() && ($tb->estado ?? 'activo') === 'activo') {
                    HorarioService::generar($t, $horariosB->map(fn ($h) => [
                        'dia_semana' => (int) $h->dia_semana,
                        'hora_inicio' => substr($h->hora_inicio, 0, 5),
                        'hora_fin' => substr($h->hora_fin, 0, 5),
                    ])->values()->all());
                }

                // Asistencia marcada (y clases canceladas, p. ej. feriados).
                $clasesB = $r->table('clases')->where('alumno_taller_id', $tb->id)
                    ->leftJoin('asistencias', 'asistencias.clase_id', '=', 'clases.id')
                    ->get(['clases.fecha', 'clases.hora_inicio', 'clases.hora_fin', 'clases.estado', 'clases.notas',
                        'asistencias.estado as asistencia', 'asistencias.observacion']);
                foreach ($clasesB as $cb) {
                    $fecha = substr($cb->fecha, 0, 10);
                    if (! $cb->asistencia && $cb->estado !== 'cancelada') {
                        continue;
                    }
                    $clase = Clase::where('alumno_taller_id', $t->id)->whereDate('fecha', $fecha)->first()
                        ?? Clase::create(['alumno_id' => $al->id, 'alumno_taller_id' => $t->id, 'maestro_id' => $t->maestro_id,
                            'especialidad_id' => $t->especialidad_id, 'periodo_id' => $oct->id, 'fecha' => $fecha,
                            'hora_inicio' => $cb->hora_inicio, 'hora_fin' => $cb->hora_fin, 'estado' => 'programada']);
                    if ($cb->asistencia) {
                        Asistencia::updateOrCreate(['clase_id' => $clase->id, 'alumno_id' => $al->id],
                            ['estado' => $cb->asistencia, 'observacion' => $cb->observacion]);
                        $clase->update(['estado' => 'realizada', 'notas' => $cb->notas ?? $clase->notas]);
                        $resumen['asistencias']++;
                    } elseif (! $clase->asistencia) {
                        $clase->update(['estado' => 'cancelada', 'notas' => $cb->notas ?? $clase->notas]);
                    }
                }

                // Su mensualidad de octubre con sus abonos.
                foreach ($r->table('pagos')->where('alumno_taller_id', $tb->id)->whereNull('deleted_at')->get() as $pb) {
                    $pago = Pago::where('alumno_taller_id', $t->id)->where('mes', $pb->mes)->where('anio', $pb->anio)->first()
                        ?? new Pago(['alumno_id' => $al->id, 'alumno_taller_id' => $t->id, 'mes' => $pb->mes, 'anio' => $pb->anio]);
                    $resumen['abonos'] += $this->copiarPago($r, $pb, $pago);
                    $resumen['pagos']++;
                }
            }

            // Matricula 2026 (una por año), si no existe ya.
            $matB = $r->table('pagos')->where('alumno_id', $alB->id)->where('tipo', 'matricula')->where('anio', 2026)->whereNull('deleted_at')->first();
            if ($matB && ! Pago::where('alumno_id', $al->id)->where('tipo', 'matricula')->where('anio', 2026)->exists()) {
                $resumen['abonos'] += $this->copiarPago($r, $matB, new Pago(['alumno_id' => $al->id, 'alumno_taller_id' => null,
                    'mes' => $matB->mes, 'anio' => $matB->anio]));
                $resumen['pagos']++;
            }

            $al->sincronizarEstadoPeriodo($oct?->id);
            $al->sincronizarTallerPrincipal();
            Log::info('RESPALDO: octubre de Danna recuperado '.json_encode($resumen));
        });
    }

    /** Copia un pago de la copia (monto, nota, abonos) sobre uno de la base actual. */
    private function copiarPago($r, $pb, Pago $pago): int
    {
        $pago->fill([
            'tipo' => $pb->tipo ?? Pago::TIPO_MENSUALIDAD,
            'concepto' => $pb->concepto,
            'monto_total' => $pb->monto_total,
            'observacion' => $pb->observacion,
        ]);
        if (! $pago->exists) {
            $pago->saldo = $pb->monto_total;
            $pago->estado = 'pendiente';
        }
        $pago->save();

        $copiados = 0;
        foreach ($r->table('pago_abonos')->where('pago_id', $pb->id)->get() as $ab) {
            $ya = PagoAbono::where('pago_id', $pago->id)->where('monto', $ab->monto)
                ->whereDate('fecha', substr($ab->fecha, 0, 10))->where('recibo_nro', $ab->recibo_nro)->exists();
            if (! $ya) {
                PagoAbono::create(['pago_id' => $pago->id, 'monto' => $ab->monto, 'fecha' => substr($ab->fecha, 0, 10),
                    'metodo_pago' => $ab->metodo_pago, 'recibo_nro' => $ab->recibo_nro, 'observacion' => $ab->observacion]);
                $copiados++;
            }
        }
        $pago->recalcular();

        return $copiados;
    }

    public function down(): void
    {
    }
};
