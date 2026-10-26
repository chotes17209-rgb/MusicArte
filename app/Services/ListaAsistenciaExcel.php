<?php

namespace App\Services;

use App\Models\Periodo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Lista de asistencia del mes para que cada maestro lleve la asistencia:
 * una hoja por maestro, una fila por alumno y taller, y en columnas las
 * fechas de clase del periodo. Las fechas en que el alumno no tiene clase
 * van sombreadas; las ya marcadas en el sistema salen con su letra.
 */
class ListaAsistenciaExcel
{
    const MORADO = '800080';
    const MORADO_SUAVE = 'F3E5F3';
    const GRIS = 'D9D9D9';

    const LETRAS = [
        'asistio' => 'A', 'tardanza' => 'A', 'falto' => 'F', 'justificado' => 'FA', 'recupero' => 'R',
    ];

    const DIAS = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

    /** @param Collection $filas filas de AsistenciaController::datosMes */
    public static function generar(Periodo $periodo, Collection $filas): Spreadsheet
    {
        $libro = new Spreadsheet();
        $libro->getProperties()->setCreator('MusicArte')->setTitle('Asistencia '.$periodo->nombre);
        $libro->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);
        $libro->removeSheetByIndex(0);

        $porMaestro = $filas->groupBy(fn ($f) => $f['taller']->maestro->nombre ?? 'Sin maestro')->sortKeys();
        $usados = [];

        foreach ($porMaestro as $maestro => $filasMaestro) {
            $hoja = new Worksheet($libro, self::tituloHoja($maestro, $usados));
            $libro->addSheet($hoja);
            self::llenarHoja($hoja, $periodo, $maestro, $filasMaestro);
        }

        if ($libro->getSheetCount() === 0) {
            $hoja = new Worksheet($libro, 'Sin alumnos');
            $libro->addSheet($hoja);
            $hoja->setCellValue('A1', 'No hay alumnos con clases en '.$periodo->nombre.' para este filtro.');
        }

        $libro->setActiveSheetIndex(0);

        return $libro;
    }

    private static function llenarHoja(Worksheet $hoja, Periodo $periodo, string $maestro, Collection $filas): void
    {
        $filas = $filas->sortBy([
            fn ($a, $b) => strcmp($a['orden'], $b['orden']),
            fn ($a, $b) => strcmp($a['taller']->alumno->nombre ?? '', $b['taller']->alumno->nombre ?? ''),
        ])->values();

        // Fechas en que al menos un alumno del maestro tiene clase.
        $fechas = $filas->flatMap(fn ($f) => $f['clases']->map(fn ($c) => $c->fecha->toDateString()))
            ->unique()->sort()->values();

        $fijas = ['N°', 'Alumno', 'Edad', 'Taller', 'Horario'];
        $colFecha = count($fijas) + 1;
        $colA = $colFecha + $fechas->count();
        $ultimaCol = $colA + 3; // A, F, R, Observaciones
        $letra = fn (int $c) => Coordinate::stringFromColumnIndex($c);
        $fin = $letra($ultimaCol);

        // Encabezado
        $hoja->setCellValue('A1', 'LISTA DE ASISTENCIA — '.mb_strtoupper($maestro));
        $hoja->mergeCells("A1:{$fin}1");
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB(self::MORADO);
        $talleres = $filas->pluck('taller.especialidad.nombre')->filter()->unique()->implode(', ');
        $hoja->setCellValue('A2', $periodo->nombre.' (del '.$periodo->fecha_inicio->format('d/m').' al '.$periodo->fecha_fin->format('d/m/Y').')'
            .($talleres ? ' · '.$talleres : '').' · '.$filas->count().' alumnos');
        $hoja->mergeCells("A2:{$fin}2");
        $hoja->setCellValue('A3', 'Marcar: A = asistió · F = faltó sin aviso · FA = faltó con aviso · R = recuperó. Casillas grises: ese día el alumno no tiene clase.');
        $hoja->mergeCells("A3:{$fin}3");
        $hoja->getStyle('A2:A3')->getFont()->getColor()->setRGB('555555');

        // Cabecera de la tabla (fila 5)
        $f = 5;
        foreach ($fijas as $i => $titulo) {
            $hoja->setCellValue($letra($i + 1).$f, $titulo);
        }
        foreach ($fechas as $i => $fecha) {
            $d = Carbon::parse($fecha);
            $hoja->setCellValue($letra($colFecha + $i).$f, self::DIAS[$d->isoWeekday()]."\n".$d->format('d/m'));
        }
        $hoja->setCellValue($letra($colA).$f, 'A');
        $hoja->setCellValue($letra($colA + 1).$f, 'F');
        $hoja->setCellValue($letra($colA + 2).$f, 'R');
        $hoja->setCellValue($letra($colA + 3).$f, 'Observaciones');
        $cab = $hoja->getStyle("A{$f}:{$fin}{$f}");
        $cab->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $cab->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::MORADO);
        $cab->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $hoja->getRowDimension($f)->setRowHeight(30);

        // Filas de alumnos
        foreach ($filas as $n => $fila) {
            $r = $f + 1 + $n;
            $t = $fila['taller'];
            $hoja->setCellValue("A{$r}", $n + 1);
            $hoja->setCellValue("B{$r}", $t->alumno->nombre ?? '—');
            $hoja->setCellValue("C{$r}", $t->alumno?->edadNumero() ?: '');
            $hoja->setCellValue("D{$r}", $t->especialidad->nombre ?? '—');
            $horario = str_replace(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'], $fila['horario']);
            if ($t->fecha_inicio && $t->fecha_inicio->gt($periodo->fecha_inicio)) {
                $horario .= ' (desde '.$t->fecha_inicio->format('d/m').')';
            }
            $hoja->setCellValue("E{$r}", $horario);

            $clases = $fila['clases']->keyBy(fn ($c) => $c->fecha->toDateString());
            foreach ($fechas as $i => $fecha) {
                $celda = $letra($colFecha + $i).$r;
                $clase = $clases->get($fecha);
                if (! $clase) {
                    $hoja->getStyle($celda)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::GRIS);
                } elseif ($clase->asistencia) {
                    $hoja->setCellValue($celda, self::LETRAS[$clase->asistencia->estado] ?? '');
                } elseif ($clase->estado === 'cancelada') {
                    $hoja->setCellValue($celda, '—');
                }
            }

            // Totales con formula: se actualizan cuando el maestro escribe.
            $desde = $letra($colFecha).$r;
            $hasta = $letra(max($colFecha, $colA - 1)).$r;
            if ($fechas->isNotEmpty()) {
                $hoja->setCellValue($letra($colA).$r, "=COUNTIF({$desde}:{$hasta},\"A\")");
                $hoja->setCellValue($letra($colA + 1).$r, "=COUNTIF({$desde}:{$hasta},\"F\")+COUNTIF({$desde}:{$hasta},\"FA\")");
                $hoja->setCellValue($letra($colA + 2).$r, "=COUNTIF({$desde}:{$hasta},\"R\")");
            }
            if ($n % 2 === 1) {
                $hoja->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::MORADO_SUAVE);
            }
        }

        $ultimaFila = $f + max(1, $filas->count());
        $tabla = $hoja->getStyle("A{$f}:{$fin}{$ultimaFila}");
        $tabla->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('999999');
        $tabla->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $hoja->getStyle($letra($colFecha).($f + 1).':'.$letra($colA + 2).$ultimaFila)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle($letra($colFecha).($f + 1).':'.$letra($colA - 1).$ultimaFila)->getFont()->setBold(true)->setSize(11);
        $hoja->getStyle("A".($f + 1).":A{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle("C".($f + 1).":C{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle("E".($f + 1).":E{$ultimaFila}")->getAlignment()->setWrapText(true);

        // Anchos de columna
        $hoja->getColumnDimension('A')->setWidth(4);
        $hoja->getColumnDimension('B')->setWidth(30);
        $hoja->getColumnDimension('C')->setWidth(6);
        $hoja->getColumnDimension('D')->setWidth(11);
        $hoja->getColumnDimension('E')->setWidth(24);
        for ($c = $colFecha; $c < $colA; $c++) {
            $hoja->getColumnDimension($letra($c))->setWidth(6.5);
        }
        for ($c = $colA; $c < $colA + 3; $c++) {
            $hoja->getColumnDimension($letra($c))->setWidth(4.5);
        }
        $hoja->getColumnDimension($letra($colA + 3))->setWidth(28);

        // Impresion: horizontal, una pagina de ancho, cabecera repetida.
        $hoja->freezePane($letra($colFecha).($f + 1));
        $hoja->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($f, $f);
        $hoja->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
    }

    /** Nombre de hoja valido en Excel (max. 31 caracteres, sin \ / ? * [ ] :) y unico. */
    private static function tituloHoja(string $nombre, array &$usados): string
    {
        $base = mb_substr(trim(preg_replace('/[\\\\\/\?\*\[\]:]/u', ' ', $nombre)) ?: 'Maestro', 0, 28);
        $titulo = $base;
        for ($i = 2; in_array(mb_strtolower($titulo), $usados, true); $i++) {
            $titulo = $base.' '.$i;
        }
        $usados[] = mb_strtolower($titulo);

        return $titulo;
    }
}
