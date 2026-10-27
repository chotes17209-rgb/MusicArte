<?php

use App\Models\Alumno;
use App\Models\AlumnoPeriodo;
use App\Models\AlumnoTaller;
use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Horario;
use App\Models\Pago;
use App\Models\PagoAbono;
use App\Models\Periodo;
use App\Services\PaseDePeriodoService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Vuelve a crear a la alumna Danna Santos Maldonado, que se elimino por
 * error, con su historial de la carga inicial (julio a setiembre: taller de
 * Piano con Noemi, lunes y miercoles 5 pm, asistencias y pagos) y su taller
 * de octubre con el mismo horario y mensualidad. Solo agrega sus registros;
 * si la alumna ya existe no hace nada.
 */
return new class extends Migration
{
    private const CLAVE = 'DANNA SANTOS MALDONADO';

    public function up(): void
    {
        if (Alumno::where('nombre', 'Danna Santos Maldonado')->exists()) {
            return;
        }

        $datos = json_decode(file_get_contents(database_path('data/administracion_2026.json')), true);
        $ficha = collect($datos['alumnos'])->firstWhere('clave', self::CLAVE);
        $filas = collect($datos['filas'])->where('alumno', self::CLAVE)->sortBy('mes')->values();
        $asistencias = collect(json_decode(file_get_contents(database_path('data/asistencia_2026.json')), true))
            ->where('alumno', self::CLAVE)->whereNotNull('estado');

        $piano = DB::table('especialidades')->where('nombre', 'Piano')->value('id');
        $noemi = DB::table('maestros')->where('nombre', 'like', 'Noem%')->value('id');
        if (! $ficha || $filas->isEmpty() || ! $piano || ! $noemi) {
            Log::warning('RECUPERAR Danna Santos: faltan datos de origen, no se hizo nada.');

            return;
        }

        DB::transaction(function () use ($ficha, $filas, $asistencias, $piano, $noemi) {
            $alumno = Alumno::create([
                'nombre' => $ficha['nombre'],
                'edad' => $ficha['edad'],
                'fecha_nacimiento' => $ficha['fecha_nacimiento'],
                'tutor' => $ficha['tutor'],
                'celular' => $ficha['celular'],
                'dni' => $ficha['dni'],
                'fecha_ingreso' => '2026-07-01',
                'especialidad_id' => $piano,
                'maestro_id' => $noemi,
                'activo' => true,
            ]);

            $ultimo = null;
            foreach ($filas as $f) {
                $periodo = Periodo::where('anio', 2026)->where('mes', $f['mes'])->first();
                if (! $periodo) {
                    continue;
                }

                $taller = AlumnoTaller::create([
                    'alumno_id' => $alumno->id,
                    'especialidad_id' => $piano,
                    'maestro_id' => $noemi,
                    'periodo_id' => $periodo->id,
                    // Los meses ya cerrados quedan como historial (igual que en la carga inicial).
                    'estado' => $f['mes'] >= 9 ? 'activo' : 'inactivo',
                    'veces_semana' => count($f['horarios']),
                    'monto_mensual' => number_format($f['total'], 2, '.', ''),
                ]);

                foreach ($f['horarios'] as $h) {
                    $horario = Horario::create([
                        'alumno_taller_id' => $taller->id,
                        'alumno_id' => $alumno->id,
                        'maestro_id' => $noemi,
                        'especialidad_id' => $piano,
                        'periodo_id' => $periodo->id,
                        'dia_semana' => $h['dia_semana'],
                        'hora_inicio' => $h['hora_inicio'],
                        'hora_fin' => $h['hora_fin'],
                        'activo' => true,
                    ]);
                    $horario->generarClases();
                }

                // Asistencia marcada en el mes.
                foreach ($asistencias->where('mes', $f['mes']) as $a) {
                    $clase = Clase::where('alumno_taller_id', $taller->id)->whereDate('fecha', $a['fecha'])->first();
                    if (! $clase) {
                        $dia = \Carbon\Carbon::parse($a['fecha'])->isoWeekday();
                        $h = collect($f['horarios'])->firstWhere('dia_semana', $dia) ?? $f['horarios'][0];
                        $clase = Clase::create([
                            'alumno_id' => $alumno->id, 'alumno_taller_id' => $taller->id,
                            'maestro_id' => $noemi, 'especialidad_id' => $piano, 'periodo_id' => $periodo->id,
                            'fecha' => $a['fecha'], 'hora_inicio' => $h['hora_inicio'], 'hora_fin' => $h['hora_fin'],
                            'estado' => 'programada',
                        ]);
                    }
                    Asistencia::updateOrCreate(
                        ['clase_id' => $clase->id, 'alumno_id' => $alumno->id],
                        ['estado' => $a['estado'], 'observacion' => $a['observacion']]
                    );
                    $clase->update(['estado' => 'realizada']);
                }

                // Mensualidad pagada del mes.
                $pago = Pago::create([
                    'alumno_id' => $alumno->id,
                    'alumno_taller_id' => $taller->id,
                    'tipo' => Pago::TIPO_MENSUALIDAD,
                    'mes' => $f['mes'],
                    'anio' => 2026,
                    'concepto' => 'Mensualidad Piano',
                    'monto_total' => $f['total'],
                    'saldo' => $f['total'],
                    'estado' => 'pendiente',
                    'observacion' => $f['observacion'],
                ]);
                $fecha = sprintf('2026-%02d-01', $f['mes']);
                foreach (['transferencia' => $f['yape'], 'efectivo' => $f['efectivo']] as $metodo => $monto) {
                    if ($monto > 0) {
                        PagoAbono::create(['pago_id' => $pago->id, 'monto' => $monto, 'fecha' => $fecha,
                            'metodo_pago' => $metodo, 'recibo_nro' => $f['recibo']]);
                    }
                }
                $pago->recalcular();

                AlumnoPeriodo::updateOrCreate(['alumno_id' => $alumno->id, 'periodo_id' => $periodo->id], ['estado' => 'activo']);
                $ultimo = $periodo;
            }

            // Octubre: el mismo taller (Piano con Noemi, lunes y miercoles 5 pm) y su mensualidad.
            $octubre = Periodo::where('anio', 2026)->where('mes', 10)->first();
            if ($ultimo && $octubre) {
                PaseDePeriodoService::copiarTalleres($alumno, $ultimo, $octubre);
            }
            $alumno->sincronizarTallerPrincipal();

            Log::info('RECUPERAR Danna Santos Maldonado: alumna recreada con id '.$alumno->id);
        });
    }

    public function down(): void
    {
        // Correccion de datos: no se revierte.
    }
};
