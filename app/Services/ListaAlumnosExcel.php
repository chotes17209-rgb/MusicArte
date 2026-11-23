<?php

namespace App\Services;

use App\Models\Periodo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Lista de alumnos en Excel, con los mismos filtros de la pantalla de
 * alumnos. Una fila por alumno y taller (quien lleva dos talleres sale con
 * cada maestro). Hoja 1: agrupada por maestro, con subtotales. Hoja 2: la
 * lista completa en una sola tabla, para filtrar u ordenar en Excel.
 */
class ListaAlumnosExcel
{
    // Mismos colores que la lista de asistencia: cabecera morada y filas
    // alternadas blanco / violeta claro.
    const MORADO = '800080';
    const MORADO_SUAVE = 'F3E5F3';
    const MORADO_MEDIO = 'E1C4E1';
    const BORDE = 'B9A3B9';

    const DIAS = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

    const COLUMNAS = [
        ['N°', 5], ['Alumno', 32], ['Edad', 6], ['Fecha de nac.', 12], ['Taller', 14], ['Horario', 24],
        ['Tutor / responsable', 28], ['Celular', 13], ['DNI', 11], ['Total a pagar', 12],
    ];

    /**
     * @param  Collection  $alumnos  alumnos con sus talleres del periodo cargados (talleres.especialidad, talleres.maestro)
     * @param  array  $filtros  textos de los filtros aplicados, para el encabezado
     */
    public static function generar(Collection $alumnos, ?Periodo $periodo, array $filtros, ?int $maestroId, ?int $especialidadId): Spreadsheet
    {
        $filas = self::filas($alumnos, $periodo, $maestroId, $especialidadId);

        $libro = new Spreadsheet();
        $libro->getProperties()->setCreator('MusicArte')->setTitle('Lista de alumnos');
        $libro->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $hoja = $libro->getActiveSheet()->setTitle('Por maestro');
        self::hojaPorMaestro($hoja, $filas, $filtros);

        $hoja2 = new Worksheet($libro, 'Lista completa');
        $libro->addSheet($hoja2);
        self::hojaCompleta($hoja2, $filas, $filtros);

        $libro->setActiveSheetIndex(0);

        return $libro;
    }

    /** Una fila por alumno y taller, ordenadas por maestro y alumno. */
    private static function filas(Collection $alumnos, ?Periodo $periodo, ?int $maestroId, ?int $especialidadId): Collection
    {
        $filas = collect();
        foreach ($alumnos as $a) {
            // Los talleres dados de baja quedan solo en el historial del alumno.
            $talleres = $a->talleres
                ->filter(fn ($t) => $t->estado === 'activo' || $t->horarios->where('activo', true)->isNotEmpty())
                ->when($maestroId, fn ($t) => $t->where('maestro_id', $maestroId))
                ->when($especialidadId, fn ($t) => $t->where('especialidad_id', $especialidadId));

            foreach ($talleres as $t) {
                $pago = $t->pagos->first(fn ($p) => $t->periodo && $p->mes == $t->periodo->mes && $p->anio == $t->periodo->anio);
                $filas->push([
                    'maestro' => $t->maestro->nombre ?? 'Sin maestro',
                    'alumno' => $a,
                    'taller' => $t->especialidad->nombre ?? '',
                    'horario' => self::horario($t),
                    'total' => $pago ? (float) $pago->monto_total : ($t->monto_mensual !== null ? (float) $t->monto_mensual : null),
                ]);
            }

            if ($talleres->isEmpty() && ! $maestroId && ! $especialidadId) {
                $filas->push(['maestro' => 'Sin taller asignado', 'alumno' => $a, 'taller' => '', 'horario' => '', 'total' => null]);
            }
        }

        return $filas->sortBy([
            fn ($x, $y) => strcmp(self::clave($x['maestro']), self::clave($y['maestro'])),
            fn ($x, $y) => strcmp(self::clave($x['alumno']->nombre), self::clave($y['alumno']->nombre)),
        ])->values();
    }

    /** Orden alfabetico sin importar tildes; "Sin ..." al final. */
    private static function clave(string $texto): string
    {
        return (str_starts_with($texto, 'Sin ') ? '~' : '').mb_strtolower(\Illuminate\Support\Str::ascii($texto));
    }

    /** "Lun y Mié 4:00 – 5:00 pm" (o cada dia con su hora si difieren). */
    public static function horario($taller): string
    {
        $hs = $taller->horarios->where('activo', true)->sortBy([['dia_semana', 'asc'], ['hora_inicio', 'asc']]);
        if ($hs->isEmpty()) {
            return '';
        }

        $hora = fn ($h) => Carbon::parse($h->hora_inicio)->format('g:i').' - '.Carbon::parse($h->hora_fin)->format('g:i a');

        return $hs->groupBy($hora)->map(function ($grupo, $rango) {
            $dias = $grupo->map(fn ($h) => self::DIAS[$h->dia_semana] ?? '')->unique()->values();
            $texto = $dias->count() > 1 ? $dias->slice(0, -1)->implode(', ').' y '.$dias->last() : $dias->first();

            return $texto.' '.$rango;
        })->implode("\n");
    }

    private static function encabezado(Worksheet $hoja, string $subtitulo, array $filtros): int
    {
        $fin = self::letra(count(self::COLUMNAS));
        $hoja->setCellValue('A1', 'MusicArte');
        $hoja->setCellValue('A2', $subtitulo);
        $hoja->setCellValue('A3', implode('   |   ', $filtros));
        $hoja->setCellValue($fin.'1', 'Fecha: '.now()->format('d/m/Y'));
        $hoja->mergeCells("A2:{$fin}2");
        $hoja->mergeCells("A3:{$fin}3");
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB(self::MORADO);
        $hoja->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $hoja->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('666666');
        $hoja->getStyle($fin.'1')->getFont()->setSize(9)->getColor()->setRGB('666666');
        $hoja->getStyle($fin.'1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $hoja->getRowDimension(1)->setRowHeight(22);

        return 5;
    }

    private static function cabeceraTabla(Worksheet $hoja, int $f, array $columnas): void
    {
        foreach ($columnas as $i => [$titulo]) {
            $celda = self::letra($i + 1).$f;
            $hoja->setCellValue($celda, $titulo);
            if (in_array($titulo, ['N°', 'Edad', 'Fecha de nac.', 'Celular', 'DNI'])) {
                $hoja->getStyle($celda)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } elseif ($titulo === 'Total a pagar') {
                $hoja->getStyle($celda)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
        $estilo = $hoja->getStyle('A'.$f.':'.self::letra(count($columnas)).$f);
        $estilo->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::MORADO);
        $estilo->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $hoja->getRowDimension($f)->setRowHeight(22);
    }

    /** Escribe los datos de un alumno desde la columna $c0. */
    private static function datos(Worksheet $hoja, int $r, int $c0, array $fila): void
    {
        $a = $fila['alumno'];
        $col = fn (int $i) => self::letra($c0 + $i).$r;
        $hoja->setCellValue($col(0), $a->nombre);
        $hoja->setCellValue($col(1), $a->edadNumero() !== null ? (int) $a->edadNumero() : ($a->edad ?: ''));
        if ($a->fecha_nacimiento) {
            $hoja->setCellValue($col(2), ExcelDate::PHPToExcel(Carbon::parse($a->fecha_nacimiento)->startOfDay()));
            $hoja->getStyle($col(2))->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        }
        $hoja->setCellValue($col(3), $fila['taller']);
        $hoja->setCellValue($col(4), $fila['horario']);
        $hoja->setCellValue($col(5), $a->tutor ?: '');
        $hoja->setCellValueExplicit($col(6), (string) ($a->celular ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $hoja->setCellValueExplicit($col(7), (string) ($a->dni ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        if ($fila['total'] !== null) {
            $hoja->setCellValue($col(8), $fila['total']);
        }
        $hoja->getStyle($col(8))->getNumberFormat()->setFormatCode('"S/" #,##0.00');
    }

    private static function hojaPorMaestro(Worksheet $hoja, Collection $filas, array $filtros): void
    {
        $columnas = self::COLUMNAS;
        $fin = self::letra(count($columnas));
        $f = self::encabezado($hoja, 'Lista de alumnos por maestro', $filtros);
        self::cabeceraTabla($hoja, $f, $columnas);
        $inicioTabla = $f;
        $f++;

        $subtotales = [];
        $cabecerasMaestro = [];
        foreach ($filas->groupBy('maestro') as $maestro => $grupo) {
            $hoja->setCellValue("A{$f}", $maestro.'  ('.$grupo->count().')');
            $hoja->mergeCells("A{$f}:{$fin}{$f}");
            $s = $hoja->getStyle("A{$f}:{$fin}{$f}");
            $cabecerasMaestro[] = "A{$f}";
            $s->getFont()->setBold(true)->setSize(11)->getColor()->setRGB(self::MORADO);
            $s->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::MORADO_MEDIO);
            $hoja->getRowDimension($f)->setRowHeight(18);
            $f++;

            $desde = $f;
            foreach ($grupo->values() as $n => $fila) {
                $hoja->setCellValue("A{$f}", $n + 1);
                self::datos($hoja, $f, 2, $fila);
                if ($n % 2 === 1) {
                    $hoja->getStyle("A{$f}:{$fin}{$f}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::MORADO_SUAVE);
                }
                $f++;
            }

            $hoja->setCellValue('I'.$f, 'Subtotal');
            $hoja->getStyle("A{$f}:{$fin}{$f}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::MORADO);
            $hoja->setCellValue($fin.$f, "=SUM({$fin}{$desde}:{$fin}".($f - 1).')');
            $hoja->getStyle("I{$f}:{$fin}{$f}")->getFont()->setBold(true);
            $hoja->getStyle($fin.$f)->getNumberFormat()->setFormatCode('"S/" #,##0.00');
            $hoja->getStyle("I{$f}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $subtotales[] = $fin.$f;
            $f += 2;
        }
        $f--;

        if ($filas->isEmpty()) {
            $hoja->setCellValue("A{$f}", 'No hay alumnos con los filtros aplicados.');
            $f += 2;
        } else {
            $alumnos = $filas->pluck('alumno.id')->unique()->count();
            $hoja->setCellValue("B{$f}", "Total de alumnos: {$alumnos}");
            $hoja->setCellValue('I'.$f, 'TOTAL');
            $hoja->setCellValue($fin.$f, '='.implode('+', $subtotales));
            $t = $hoja->getStyle("A{$f}:{$fin}{$f}");
            $t->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $t->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::MORADO);
            $hoja->getStyle($fin.$f)->getNumberFormat()->setFormatCode('"S/" #,##0.00');
            $hoja->getStyle("I{$f}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        self::acabado($hoja, $columnas, $inicioTabla, $f);
        foreach ($cabecerasMaestro as $celda) {
            $hoja->getStyle($celda)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        }
    }

    private static function hojaCompleta(Worksheet $hoja, Collection $filas, array $filtros): void
    {
        $columnas = array_merge([['N°', 5], ['Maestro', 22]], array_slice(self::COLUMNAS, 1));
        $fin = self::letra(count($columnas));
        $f = self::encabezado($hoja, 'Lista completa de alumnos', $filtros);
        self::cabeceraTabla($hoja, $f, $columnas);
        $inicioTabla = $f;

        foreach ($filas as $n => $fila) {
            $r = $f + 1 + $n;
            $hoja->setCellValue("A{$r}", $n + 1);
            $hoja->setCellValue("B{$r}", $fila['maestro']);
            self::datos($hoja, $r, 3, $fila);
            if ($n % 2 === 1) {
                $hoja->getStyle("A{$r}:{$fin}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::MORADO_SUAVE);
            }
        }
        $ultima = $f + max(1, $filas->count());
        if ($filas->isNotEmpty()) {
            $hoja->setAutoFilter("A{$f}:{$fin}{$ultima}");
        }

        self::acabado($hoja, $columnas, $inicioTabla, $ultima);
    }

    private static function acabado(Worksheet $hoja, array $columnas, int $inicio, int $fin): void
    {
        $ultimaCol = self::letra(count($columnas));
        $tabla = $hoja->getStyle("A{$inicio}:{$ultimaCol}{$fin}");
        $tabla->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDE);
        $tabla->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $hoja->setShowGridlines(false);

        foreach ($columnas as $i => [$titulo, $ancho]) {
            $letra = self::letra($i + 1);
            $hoja->getColumnDimension($letra)->setWidth($ancho);
            if (in_array($titulo, ['N°', 'Edad', 'Fecha de nac.', 'Celular', 'DNI'])) {
                $hoja->getStyle("{$letra}".($inicio + 1).":{$letra}{$fin}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            if (in_array($titulo, ['Horario', 'Tutor / responsable'])) {
                $hoja->getStyle("{$letra}".($inicio + 1).":{$letra}{$fin}")->getAlignment()->setWrapText(true);
            }
        }

        $hoja->freezePane('A'.($inicio + 1));
        $hoja->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($inicio, $inicio);
        $hoja->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
    }

    private static function letra(int $c): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
    }
}
