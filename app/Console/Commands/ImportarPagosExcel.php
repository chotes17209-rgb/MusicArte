<?php

namespace App\Console\Commands;

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Especialidad;
use App\Models\Maestro;
use App\Models\Pago;
use App\Models\PagoAbono;
use App\Models\Periodo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa la hoja "PAGOS" de ADMINISTRACION_2026.xlsx: crea/actualiza
 * alumnos, sus talleres y sus pagos mes a mes, de Enero a Setiembre.
 *
 * El Excel esta organizado en bloques por mes (ENERO, FEBRERO, ... hasta
 * SETIEMBRE), y Enero/Febrero ademas vienen partidos en MAÑANAS/TARDES
 * (mismo mes/periodo, solo cambia el turno). Cada fila de alumno dentro
 * de un bloque es UN pago de UN taller en ESE mes.
 *
 * Uso (subir primero el .xlsx al servidor, por ejemplo a
 * storage/app/private/administracion-2026.xlsx via el shell de Render,
 * el archivo NO se guarda en git porque trae datos personales reales):
 *
 *   php artisan pagos:importar-excel storage/app/private/administracion-2026.xlsx --dry-run
 *   php artisan pagos:importar-excel storage/app/private/administracion-2026.xlsx
 */
class ImportarPagosExcel extends Command
{
    protected $signature = 'pagos:importar-excel
        {archivo : Ruta al .xlsx en el servidor}
        {--hoja=PAGOS : Nombre de la hoja a leer}
        {--anio=2026 : Anio de los periodos Enero-Setiembre}
        {--dry-run : Solo mostrar el resumen, sin guardar nada en la base de datos}';

    protected $description = 'Importa los pagos de Enero a Setiembre desde el Excel de administracion (hoja PAGOS).';

    // Columnas (1-indexadas, tal cual vienen en ADMINISTRACION_2026.xlsx).
    private const COL_NRO = 2;
    private const COL_NOMBRE = 3;
    private const COL_EDAD = 4;
    private const COL_ESPECIALIDAD = 5;
    private const COL_MAESTRO = 6;
    private const COL_FNAC = 9;
    private const COL_TUTOR = 10;
    private const COL_CELULAR = 11;
    private const COL_DNI = 12;
    private const COL_TOTAL = 13;
    private const COL_YAPE = 15;
    private const COL_EFECTIVO = 16;
    private const COL_SALDO = 17;
    private const COL_RECIBO = 18;

    private const MESES = [
        'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4, 'MAYO' => 5, 'JUNIO' => 6,
        'JULIO' => 7, 'AGOSTO' => 8, 'SETIEMBRE' => 9, 'SEPTIEMBRE' => 9,
    ];

    // Especialidades canonicas (las mismas 7 de EspecialidadSeeder). Todo lo
    // que no calce con esto (ej. "INICIACION MUSICAL") se crea como
    // especialidad nueva la primera vez que aparece.
    private const ESPECIALIDADES_CANONICAS = [
        'BATERIA' => 'Bateria', 'BATERIAJE' => 'Bateria', // typo visto en el excel
        'CANTO' => 'Canto',
        'FLAUTA' => 'Flauta',
        'GUITARRA' => 'Guitarra',
        'PIANO' => 'Piano', 'PIANON' => 'Piano', // typo visto en el excel
        'SAXOFON' => 'Saxofon',
        'VIOLIN' => 'Violin',
    ];

    // Correcciones de maestros con typo claro (mismo profesor, nombre mal
    // tipeado). "ARIANA" se deja aparte a proposito: no hay forma de saber
    // con certeza si es la misma persona que "ARIAM"/"ARIAN".
    private const MAESTROS_CORRECCION = [
        'ARIAN' => 'Ariam',
        'ARIAM' => 'Ariam',
    ];

    private array $warnings = [];
    private array $stats = [
        'filas' => 0, 'alumnos_nuevos' => 0, 'alumnos_actualizados' => 0,
        'especialidades_nuevas' => 0, 'maestros_nuevos' => 0,
        'talleres_nuevos' => 0, 'pagos_nuevos' => 0, 'pagos_actualizados' => 0,
    ];

    public function handle(): int
    {
        $ruta = $this->argument('archivo');
        if (! is_file($ruta)) {
            $this->error("No se encontro el archivo: {$ruta}");

            return self::FAILURE;
        }

        $anio = (int) $this->option('anio');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Leyendo '.basename($ruta).' ...');
        $spreadsheet = IOFactory::load($ruta);
        $hoja = $spreadsheet->getSheetByName($this->option('hoja'));
        if (! $hoja) {
            $this->error('No existe la hoja "'.$this->option('hoja').'" en ese archivo.');

            return self::FAILURE;
        }

        // Periodos Enero-Setiembre: deben existir (via PeriodoSeeder). Si
        // falta alguno, se avisa y se corta antes de tocar nada.
        $periodosPorMes = Periodo::where('anio', $anio)->whereIn('mes', range(1, 9))->get()->keyBy('mes');
        for ($m = 1; $m <= 9; $m++) {
            if (! $periodosPorMes->has($m)) {
                $this->error("Falta el periodo {$m}/{$anio}. Corre primero: php artisan db:seed --class=Database\\Seeders\\PeriodoSeeder");

                return self::FAILURE;
            }
        }

        try {
            DB::transaction(function () use ($hoja, $anio, $periodosPorMes, $dryRun) {
                $this->procesarHoja($hoja, $anio, $periodosPorMes);

                if ($dryRun) {
                    // Fuerza el rollback: en dry-run no se guarda nada, solo
                    // se calculan las estadisticas de lo que HABRIA pasado.
                    throw new \RuntimeException('__DRY_RUN__');
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== '__DRY_RUN__') {
                throw $e;
            }
        }

        $this->mostrarResumen($dryRun);

        return self::SUCCESS;
    }

    private function procesarHoja($hoja, int $anio, $periodosPorMes): void
    {
        $mesActual = null;
        $filaMax = $hoja->getHighestRow();

        for ($fila = 1; $fila <= $filaMax; $fila++) {
            $colB = trim((string) $hoja->getCell([self::COL_NRO, $fila])->getCalculatedValue());
            $colC = trim((string) $hoja->getCell([self::COL_NOMBRE, $fila])->getCalculatedValue());

            $mesDetectado = $this->detectarMes($colC);
            if ($mesDetectado) {
                $mesActual = $mesDetectado;

                continue;
            }

            // Fila de encabezado de tabla ("Nº | NOMBRE | ...").
            if (in_array(mb_strtoupper($colB), ['Nº', 'N°'], true)) {
                continue;
            }

            if ($colC === '' || $mesActual === null) {
                continue;
            }

            $this->procesarFilaAlumno($hoja, $fila, $mesActual, $periodosPorMes[$mesActual]);
        }
    }

    private function detectarMes(string $texto): ?int
    {
        if ($texto === '') {
            return null;
        }
        $limpio = mb_strtoupper(trim(preg_replace('/[-–].*$/u', '', $texto)));

        return self::MESES[$limpio] ?? null;
    }

    private function procesarFilaAlumno($hoja, int $fila, int $mes, Periodo $periodo): void
    {
        $this->stats['filas']++;

        $nombreExcel = trim((string) $hoja->getCell([self::COL_NOMBRE, $fila])->getCalculatedValue());
        $edad = trim((string) $hoja->getCell([self::COL_EDAD, $fila])->getCalculatedValue()) ?: null;
        $especialidadTexto = trim((string) $hoja->getCell([self::COL_ESPECIALIDAD, $fila])->getCalculatedValue());
        $maestroTexto = trim((string) $hoja->getCell([self::COL_MAESTRO, $fila])->getCalculatedValue());
        $fnac = $this->leerFecha($hoja, self::COL_FNAC, $fila);
        $tutor = trim((string) $hoja->getCell([self::COL_TUTOR, $fila])->getCalculatedValue()) ?: null;
        $celular = trim((string) $hoja->getCell([self::COL_CELULAR, $fila])->getCalculatedValue()) ?: null;
        $dni = trim((string) $hoja->getCell([self::COL_DNI, $fila])->getCalculatedValue()) ?: null;
        $recibo = trim((string) $hoja->getCell([self::COL_RECIBO, $fila])->getCalculatedValue()) ?: null;

        $total = $this->leerNumero($hoja, self::COL_TOTAL, $fila, $nombreExcel, $mes);
        $yape = $this->leerNumero($hoja, self::COL_YAPE, $fila, $nombreExcel, $mes) ?? 0.0;
        $efectivo = $this->leerNumero($hoja, self::COL_EFECTIVO, $fila, $nombreExcel, $mes) ?? 0.0;
        $saldoExcel = $this->leerNumero($hoja, self::COL_SALDO, $fila, $nombreExcel, $mes) ?? 0.0;

        $alumno = $this->buscarOCrearAlumno($nombreExcel, $dni, $edad, $fnac, $tutor, $celular);

        $especialidad = $especialidadTexto !== '' ? $this->buscarOCrearEspecialidad($especialidadTexto) : null;
        $maestro = $maestroTexto !== '' ? $this->buscarOCrearMaestro($maestroTexto) : null;

        $taller = null;
        if ($especialidad) {
            $taller = AlumnoTaller::where('alumno_id', $alumno->id)
                ->where('especialidad_id', $especialidad->id)
                ->where('periodo_id', $periodo->id)
                ->first();

            if ($taller) {
                $taller->update(['maestro_id' => $maestro?->id, 'estado' => 'activo']);
            } else {
                $taller = AlumnoTaller::create([
                    'alumno_id' => $alumno->id,
                    'especialidad_id' => $especialidad->id,
                    'maestro_id' => $maestro?->id,
                    'periodo_id' => $periodo->id,
                    'estado' => 'activo',
                ]);
                $this->stats['talleres_nuevos']++;
            }

            $alumno->sincronizarTallerPrincipal();
            $alumno->sincronizarEstadoPeriodo($periodo->id);
        } else {
            $this->warnings[] = "Fila {$fila} ({$nombreExcel}, mes {$mes}): sin especialidad en el Excel, el pago queda sin taller asociado.";
        }

        $concepto = 'Mensualidad'.($especialidad ? ' '.$especialidad->nombre : '');

        $pago = Pago::where('alumno_id', $alumno->id)
            ->where('alumno_taller_id', $taller?->id)
            ->where('mes', $mes)
            ->where('anio', $periodo->anio)
            ->first();

        $esNuevo = ! $pago;

        $pago = Pago::updateOrCreate(
            [
                'alumno_id' => $alumno->id,
                'alumno_taller_id' => $taller?->id,
                'mes' => $mes,
                'anio' => $periodo->anio,
            ],
            [
                'concepto' => $concepto,
                'monto_total' => $total ?? 0,
                'recibo_nro' => $recibo,
            ]
        );

        $esNuevo ? $this->stats['pagos_nuevos']++ : $this->stats['pagos_actualizados']++;

        // Se recrean los abonos desde cero (comando idempotente: correrlo
        // dos veces con el mismo archivo no debe duplicar abonos).
        $pago->abonos()->delete();

        $fecha = $periodo->fecha_inicio->toDateString();
        if ($yape > 0) {
            PagoAbono::create(['pago_id' => $pago->id, 'monto' => $yape, 'fecha' => $fecha, 'metodo_pago' => 'transferencia', 'recibo_nro' => $recibo, 'observacion' => 'Importado de administracion 2026 (Excel).']);
        }
        if ($efectivo > 0) {
            PagoAbono::create(['pago_id' => $pago->id, 'monto' => $efectivo, 'fecha' => $fecha, 'metodo_pago' => 'efectivo', 'recibo_nro' => $recibo, 'observacion' => 'Importado de administracion 2026 (Excel).']);
        }
        // El Excel dice "saldo 0" (pagado) pero no desglosa yape/efectivo:
        // se registra igual el monto pagado para que no quede como deuda.
        if ($yape <= 0 && $efectivo <= 0 && ($total ?? 0) > 0 && $saldoExcel <= 0) {
            PagoAbono::create(['pago_id' => $pago->id, 'monto' => $total, 'fecha' => $fecha, 'metodo_pago' => 'efectivo', 'recibo_nro' => $recibo, 'observacion' => 'Importado de administracion 2026 (Excel): pagado, sin desglose de metodo en el original.']);
        }

        $pago->recalcular();
    }

    private function buscarOCrearAlumno(string $nombreExcel, ?string $dni, ?string $edad, ?string $fnac, ?string $tutor, ?string $celular): Alumno
    {
        $alumno = null;

        if ($dni) {
            $alumno = Alumno::where('dni', $dni)->first();
        }

        if (! $alumno) {
            $alumno = Alumno::whereRaw('UPPER(nombre) = ?', [mb_strtoupper($nombreExcel, 'UTF-8')])->first();
        }

        if (! $alumno) {
            $alumno = Alumno::create([
                'nombre' => $this->tituloCase($nombreExcel),
                'edad' => $edad,
                'fecha_nacimiento' => $fnac,
                'tutor' => $tutor,
                'celular' => $celular,
                'dni' => $dni,
                'activo' => true,
            ]);
            $this->stats['alumnos_nuevos']++;

            return $alumno;
        }

        // Alumno ya existia (por ejemplo, cargado por AlumnoSeeder o por un
        // mes anterior de esta misma importacion): solo se completan los
        // campos que esten vacios, nunca se pisa un dato ya cargado.
        $cambios = array_filter([
            'edad' => $alumno->edad ?: $edad,
            'fecha_nacimiento' => $alumno->fecha_nacimiento ?: $fnac,
            'tutor' => $alumno->tutor ?: $tutor,
            'celular' => $alumno->celular ?: $celular,
            'dni' => $alumno->dni ?: $dni,
        ], fn ($v) => $v !== null && $v !== '');

        if ($cambios) {
            $alumno->update($cambios);
            $this->stats['alumnos_actualizados']++;
        }

        return $alumno;
    }

    private function buscarOCrearEspecialidad(string $texto): Especialidad
    {
        $limpio = mb_strtoupper(trim($texto));
        $sinAcentos = mb_strtoupper((string) iconv('UTF-8', 'ASCII//TRANSLIT', $limpio));

        $nombreCanonico = self::ESPECIALIDADES_CANONICAS[$sinAcentos] ?? null;

        if ($nombreCanonico) {
            return Especialidad::where('nombre', $nombreCanonico)->first()
                ?? Especialidad::create(['nombre' => $nombreCanonico, 'activo' => true]);
        }

        // No calzo con ninguna canonica (ej. "INICIACION MUSICAL"): se
        // busca/crea tal cual, en Titulo Case.
        $nombreNuevo = $this->tituloCase($texto);
        $existente = Especialidad::whereRaw('UPPER(nombre) = ?', [$limpio])->first();
        if ($existente) {
            return $existente;
        }

        $nueva = Especialidad::create(['nombre' => $nombreNuevo, 'activo' => true]);
        $this->stats['especialidades_nuevas']++;
        $this->warnings[] = "Especialidad nueva creada: \"{$nombreNuevo}\" (no estaba en las 7 especialidades base).";

        return $nueva;
    }

    private function buscarOCrearMaestro(string $texto): Maestro
    {
        $limpio = mb_strtoupper(trim($texto));
        $sinAcentos = mb_strtoupper((string) iconv('UTF-8', 'ASCII//TRANSLIT', $limpio));
        $corregido = self::MAESTROS_CORRECCION[$sinAcentos] ?? $this->tituloCase($texto);

        $existente = Maestro::whereRaw('UPPER(nombre) = ?', [mb_strtoupper($corregido, 'UTF-8')])->first();
        if ($existente) {
            return $existente;
        }

        $nuevo = Maestro::create(['nombre' => $corregido, 'activo' => true]);
        $this->stats['maestros_nuevos']++;
        $this->warnings[] = "Maestro nuevo creado: \"{$corregido}\" (no estaba en la plana docente de MaestroSeeder).";

        return $nuevo;
    }

    private function leerNumero($hoja, int $col, int $fila, string $nombre, int $mes): ?float
    {
        $valor = $hoja->getCell([$col, $fila])->getCalculatedValue();
        if ($valor === null || $valor === '') {
            return null;
        }
        if (is_numeric($valor)) {
            return (float) $valor;
        }

        $this->warnings[] = "Fila {$fila} ({$nombre}, mes {$mes}): valor no numerico \"{$valor}\" en una columna de monto, se tomo como 0.";

        return 0.0;
    }

    private function leerFecha($hoja, int $col, int $fila): ?string
    {
        $celda = $hoja->getCell([$col, $fila]);
        $valor = $celda->getCalculatedValue();
        if (! $valor) {
            return null;
        }
        if (is_numeric($valor)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        try {
            return \Carbon\Carbon::parse((string) $valor)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function tituloCase(string $texto): string
    {
        return mb_convert_case(mb_strtolower(trim(preg_replace('/\s+/', ' ', $texto))), MB_CASE_TITLE, 'UTF-8');
    }

    private function mostrarResumen(bool $dryRun): void
    {
        $this->newLine();
        $this->info($dryRun ? '=== RESUMEN (dry-run, no se guardo nada) ===' : '=== IMPORTACION COMPLETADA ===');
        $this->table(['Metrica', 'Cantidad'], [
            ['Filas de pago procesadas', $this->stats['filas']],
            ['Alumnos nuevos', $this->stats['alumnos_nuevos']],
            ['Alumnos con datos completados', $this->stats['alumnos_actualizados']],
            ['Especialidades nuevas', $this->stats['especialidades_nuevas']],
            ['Maestros nuevos', $this->stats['maestros_nuevos']],
            ['Talleres nuevos', $this->stats['talleres_nuevos']],
            ['Pagos nuevos', $this->stats['pagos_nuevos']],
            ['Pagos actualizados', $this->stats['pagos_actualizados']],
        ]);

        if ($this->warnings) {
            $this->newLine();
            $this->warn(count($this->warnings).' advertencia(s):');
            foreach (array_slice($this->warnings, 0, 50) as $w) {
                $this->line(' - '.$w);
            }
            if (count($this->warnings) > 50) {
                $this->line(' ... y '.(count($this->warnings) - 50).' mas.');
            }
        }
    }
}
