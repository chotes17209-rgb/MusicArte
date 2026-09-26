<?php

use App\Models\Alumno;
use App\Models\AlumnoPeriodo;
use App\Models\AlumnoTaller;
use App\Models\Clase;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Maestro;
use App\Models\Pago;
use App\Models\PagoAbono;
use App\Models\Periodo;
use App\Services\HorarioService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Carga en la app TODA la hoja PAGOS de ADMINISTRACION_2026.xlsx, periodo
 * por periodo (Enero a Setiembre 2026): alumnos, talleres, horarios (con
 * sus clases en el calendario), pagos y abonos.
 *
 * Es una migracion (y no un seeder) a proposito: el Dockerfile corre
 * "migrate --seed" en cada deploy, y esto debe correr UNA sola vez; si
 * corriera en cada deploy pisaria lo que se registre a mano despues.
 *
 * El Excel ya viene convertido y limpio en database/data/administracion_2026.json
 * (nombres unificados, horarios en texto libre ya interpretados, montos
 * con errores corregidos). Antes de cargar se borran los talleres,
 * horarios, clases y pagos de Enero-Setiembre 2026 (y los que quedaron
 * sin periodo de los seeders viejos), para que la app quede exactamente
 * con la data del Excel sin duplicados. Los alumnos existentes NO se
 * borran: se reusan (buscandolos por nombre) y se actualizan.
 */
return new class extends Migration
{
    private const COLORES = [
        'Bateria' => '#e67e22', 'Canto' => '#d81b60', 'Flauta' => '#2980b9', 'Guitarra' => '#c0392b',
        'Piano' => '#3d2c8d', 'Saxofon' => '#8e44ad', 'Violin' => '#16a085', 'Iniciacion Musical' => '#f39c12',
    ];

    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Setiembre',
    ];

    public function up(): void
    {
        $data = json_decode(file_get_contents(database_path('data/administracion_2026.json')), true);
        $anio = $data['anio'];

        DB::transaction(function () use ($data, $anio) {
            $periodos = $this->periodos($anio);
            $periodoIds = $periodos->pluck('id')->all();

            $this->limpiar($anio, $periodoIds);

            $especialidades = collect($data['filas'])->pluck('especialidad')->filter()->unique()
                ->mapWithKeys(fn ($n) => [$n => $this->especialidad($n)]);
            $maestros = collect($data['filas'])->pluck('maestro')->filter()->unique()
                ->mapWithKeys(fn ($n) => [$n => Maestro::firstOrCreate(['nombre' => $n], ['activo' => true])]);

            $alumnos = $this->alumnos($data['alumnos']);

            foreach ($data['filas'] as $fila) {
                $this->importarFila(
                    $fila,
                    $alumnos[$fila['alumno']],
                    $periodos[$fila['mes']],
                    $especialidades[$fila['especialidad']],
                    $maestros[$fila['maestro']]
                );
            }

            $this->cerrarHistorial($alumnos, $periodos);
        });
    }

    public function down(): void
    {
        // Carga de datos: no se revierte automaticamente.
    }

    private function periodos(int $anio)
    {
        return collect(self::MESES)->mapWithKeys(function ($nombre, $mes) use ($anio) {
            $periodo = Periodo::where('mes', $mes)->where('anio', $anio)->first();
            if (! $periodo) {
                [$inicio, $fin] = Periodo::sugerirRango($mes, $anio);
                $periodo = Periodo::create([
                    'nombre' => $nombre.' '.$anio, 'mes' => $mes, 'anio' => $anio,
                    'fecha_inicio' => $inicio, 'fecha_fin' => $fin, 'activo' => $mes === 9,
                ]);
            }

            return [$mes => $periodo];
        });
    }

    private function limpiar(int $anio, array $periodoIds): void
    {
        $horarioIds = Horario::whereIn('periodo_id', $periodoIds)->orWhereNull('periodo_id')->pluck('id');

        Clase::whereIn('periodo_id', $periodoIds)->orWhereIn('horario_id', $horarioIds)->delete();
        Horario::whereIn('id', $horarioIds)->delete();
        Pago::where('anio', $anio)->whereBetween('mes', [1, 9])->delete();
        AlumnoTaller::whereIn('periodo_id', $periodoIds)->orWhereNull('periodo_id')->delete();
        AlumnoPeriodo::whereIn('periodo_id', $periodoIds)->delete();
    }

    private function especialidad(string $nombre): Especialidad
    {
        return Especialidad::firstOrCreate(
            ['nombre' => $nombre],
            ['color' => self::COLORES[$nombre] ?? '#3d2c8d', 'precio_mensual' => 230.0, 'activo' => true]
        );
    }

    /** Crea o actualiza cada alumno del Excel, reusando los que ya existan con el mismo nombre. */
    private function alumnos(array $filas): array
    {
        $existentes = Alumno::all()->groupBy(fn ($a) => $this->clave($a->nombre));
        $resultado = [];

        foreach ($filas as $a) {
            $alumno = null;
            foreach (array_merge([$a['clave']], $a['alias']) as $clave) {
                $alumno = $existentes->get($clave)?->first();
                if ($alumno) {
                    break;
                }
            }

            $datos = array_filter([
                'edad' => $a['edad'],
                'fecha_nacimiento' => $a['fecha_nacimiento'],
                'tutor' => $a['tutor'],
                'celular' => $a['celular'],
                'dni' => $a['dni'],
                'diagnostico' => $a['diagnostico'],
            ], fn ($v) => $v !== null && $v !== '');
            $datos['nombre'] = $a['nombre'];
            $datos['activo'] = $a['activo'];

            if ($alumno) {
                $alumno->update($datos);
            } else {
                $alumno = Alumno::create($datos + ['fecha_ingreso' => sprintf('2026-%02d-01', min($a['meses']))]);
            }

            $resultado[$a['clave']] = $alumno;
        }

        return $resultado;
    }

    private function importarFila(array $fila, Alumno $alumno, Periodo $periodo, Especialidad $especialidad, Maestro $maestro): void
    {
        $maestro->especialidades()->syncWithoutDetaching([
            $especialidad->id => ['tarifa_hora' => $especialidad->nombre === 'Bateria' ? 15 : 10],
        ]);

        $taller = AlumnoTaller::firstOrCreate(
            ['alumno_id' => $alumno->id, 'especialidad_id' => $especialidad->id, 'maestro_id' => $maestro->id, 'periodo_id' => $periodo->id],
            ['estado' => 'activo']
        );

        if ($fila['horarios']) {
            HorarioService::generar($taller, $fila['horarios']);
        }

        // Fila con TOTAL 0 y sin pagos (ej. alumno becado ese mes): hay
        // taller y horario, pero no se genera una deuda en cero.
        if ($fila['total'] <= 0 && $fila['yape'] <= 0 && $fila['efectivo'] <= 0) {
            return;
        }

        $observacion = trim(($fila['observacion'] ? $fila['observacion'].' | ' : '')
            .'Importado de ADMINISTRACION_2026.xlsx (hoja PAGOS, fila '.$fila['fila'].').');

        $pago = Pago::create([
            'alumno_id' => $alumno->id,
            'alumno_taller_id' => $taller->id,
            'mes' => $periodo->mes,
            'anio' => $periodo->anio,
            'concepto' => 'Mensualidad '.$especialidad->nombre,
            'monto_total' => $fila['total'],
            'recibo_nro' => $fila['recibo'],
            'observacion' => $observacion,
        ]);

        $fecha = $periodo->fecha_inicio->toDateString();
        $abonar = fn ($monto, $metodo, $obs = null) => PagoAbono::create([
            'pago_id' => $pago->id, 'monto' => $monto, 'fecha' => $fecha, 'metodo_pago' => $metodo,
            'recibo_nro' => $fila['recibo'], 'observacion' => $obs,
        ]);

        if ($fila['yape'] > 0) {
            $abonar($fila['yape'], 'transferencia');
        }
        if ($fila['efectivo'] > 0) {
            $abonar($fila['efectivo'], 'efectivo');
        }
        // El Excel marca SALDO 0 (pagado) pero no dice si fue yape o efectivo.
        if ($fila['yape'] <= 0 && $fila['efectivo'] <= 0 && $fila['total'] > 0
            && $fila['saldo_excel'] !== null && $fila['saldo_excel'] <= 0) {
            $abonar($fila['total'], 'efectivo', 'Pagado segun el Excel, sin detalle de metodo de pago.');
        }

        $pago->recalcular();
    }

    /**
     * Historial por periodo: el alumno queda "activo" en cada mes en el que
     * aparece en el Excel. Los talleres de meses ya cerrados (Enero-Agosto)
     * quedan como historial (inactivos); solo los de Setiembre siguen activos.
     */
    private function cerrarHistorial(array $alumnos, $periodos): void
    {
        $actual = $periodos[9];

        foreach ($alumnos as $alumno) {
            foreach ($alumno->talleres()->pluck('periodo_id')->unique() as $periodoId) {
                AlumnoPeriodo::updateOrCreate(
                    ['alumno_id' => $alumno->id, 'periodo_id' => $periodoId],
                    ['estado' => 'activo']
                );
            }
        }

        AlumnoTaller::whereIn('periodo_id', $periodos->except(9)->pluck('id'))->update(['estado' => 'inactivo']);

        foreach ($alumnos as $alumno) {
            $principal = $alumno->talleres()->where('periodo_id', $actual->id)->oldest('id')->first()
                ?? $alumno->talleres()->join('periodos', 'periodos.id', '=', 'alumno_talleres.periodo_id')
                    ->orderByDesc('periodos.mes')->orderBy('alumno_talleres.id')
                    ->select('alumno_talleres.*')->first();

            $alumno->forceFill([
                'especialidad_id' => $principal?->especialidad_id,
                'maestro_id' => $principal?->maestro_id,
            ])->saveQuietly();
        }
    }

    private function clave(string $nombre): string
    {
        return Str::upper(preg_replace('/\s+/', ' ', trim(Str::ascii($nombre))));
    }
};
