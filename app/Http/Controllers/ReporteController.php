<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\CajaChica;
use App\Models\Clase;
use App\Models\Egreso;
use App\Models\Especialidad;
use App\Models\Maestro;
use App\Models\Pago;
use App\Models\Planilla;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    /** Reporte: alumnos activos por especialidad. */
    public function alumnosPorEspecialidad()
    {
        $data = Especialidad::withCount(['alumnos' => fn ($q) => $q->where('activo', true)])
            ->orderByDesc('alumnos_count')->get();

        return view('reportes.alumnos-especialidad', ['data' => $data]);
    }

    /**
     * Reporte de asistencia mensual (seccion 17). Si se filtra por maestro,
     * solo se consideran las clases dictadas por ese maestro y solo se
     * listan los alumnos que tuvieron al menos una clase con el.
     */
    public function asistenciaMensual(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);
        $maestroId = $request->get('maestro_id');

        $data = Alumno::activos()->with(['asistencias' => function ($q) use ($mes, $anio, $maestroId) {
            $q->whereHas('clase', function ($c) use ($mes, $anio, $maestroId) {
                $c->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
                if ($maestroId) {
                    $c->where('maestro_id', $maestroId);
                }
            });
        }])->get()->map(function ($alumno) {
            $total = $alumno->asistencias->count();
            $asistio = $alumno->asistencias->where('estado', 'asistio')->count();
            $faltas = $alumno->asistencias->where('estado', 'falto')->count();
            $tardanzas = $alumno->asistencias->where('estado', 'tardanza')->count();
            return [
                'alumno' => $alumno->nombre,
                'total' => $total,
                'asistio' => $asistio,
                'faltas' => $faltas,
                'tardanzas' => $tardanzas,
                'porcentaje' => $total > 0 ? round($asistio / $total * 100, 1) : 0,
            ];
        })->when($maestroId, fn ($collection) => $collection->filter(fn ($row) => $row['total'] > 0)->values());

        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        return view('reportes.asistencia-mensual', compact('data', 'mes', 'anio', 'maestros', 'maestroId'));
    }

    /** Reporte: ingresos vs egresos por mes. */
    public function ingresosEgresos(Request $request)
    {
        $anio = $request->get('anio', now()->year);

        $data = collect(range(1, 12))->map(function ($mes) use ($anio) {
            $ingresos = Pago::where('mes', $mes)->where('anio', $anio)->sum('monto_total');
            $egresos = Egreso::whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('total');
            $cajaChica = CajaChica::whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('monto');
            $planilla = Planilla::where('mes', $mes)->where('anio', $anio)->sum('monto');

            return [
                'mes' => \App\Models\Pago::MESES[$mes],
                'ingresos' => $ingresos,
                'egresos' => $egresos + $cajaChica + $planilla,
                'balance' => $ingresos - ($egresos + $cajaChica + $planilla),
            ];
        });

        return view('reportes.ingresos-egresos', compact('data', 'anio'));
    }

    /**
     * Reporte: alumnos con pagos pendientes o parciales (seccion 14).
     * Filtros por periodo, maestro, taller (especialidad) y estado.
     */
    public function pagosPendientes(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $query = Pago::with(['alumno', 'alumnoTaller.especialidad', 'alumnoTaller.maestro'])
            ->where('mes', $mes)->where('anio', $anio);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            // Por defecto (sin filtro de estado) se muestra todo lo que no
            // esta 100% pagado: pendientes y a cuenta.
            $query->where('saldo', '>', 0);
        }

        if ($request->filled('especialidad_id')) {
            $especialidadId = $request->especialidad_id;
            $query->whereHas('alumnoTaller', fn ($q) => $q->where('especialidad_id', $especialidadId));
        }

        if ($request->filled('maestro_id')) {
            $maestroId = $request->maestro_id;
            $query->whereHas('alumnoTaller', fn ($q) => $q->where('maestro_id', $maestroId));
        }

        $data = $query->orderByDesc('saldo')->get();

        $especialidades = Especialidad::orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        return view('reportes.pagos-pendientes', compact('data', 'mes', 'anio', 'especialidades', 'maestros'));
    }

    /**
     * Reporte: historial de pagos de todo un anio (seccion 13). Permite
     * ver, mes a mes, cuanto se cobro, cuanto quedo pendiente y cuanto
     * esta "a cuenta", y quien debe.
     */
    public function pagosAnual(Request $request)
    {
        $anio = $request->get('anio', now()->year);

        $pagosDelAnio = Pago::with('alumno')->where('anio', $anio)->get();

        $porMes = collect(range(1, 12))->map(function ($mes) use ($pagosDelAnio) {
            $pagosMes = $pagosDelAnio->where('mes', $mes);

            return [
                'mes' => $mes,
                'label' => Pago::MESES[$mes],
                'facturado' => $pagosMes->sum('monto_total'),
                'cobrado' => $pagosMes->sum(fn ($p) => $p->monto_total - $p->saldo),
                'pendiente' => $pagosMes->sum('saldo'),
                'cant_pagado' => $pagosMes->where('estado', 'pagado')->count(),
                'cant_a_cuenta' => $pagosMes->where('estado', 'a_cuenta')->count(),
                'cant_pendiente' => $pagosMes->where('estado', 'pendiente')->count(),
            ];
        });

        // Deudores del anio: alumnos que en algun mes de ese anio quedaron
        // con saldo pendiente, y cuanto deben en total.
        $deudores = $pagosDelAnio->where('saldo', '>', 0)
            ->groupBy('alumno_id')
            ->map(function ($pagos) {
                return [
                    'alumno' => optional($pagos->first()->alumno)->nombre,
                    'meses_pendientes' => $pagos->pluck('mes')->map(fn ($m) => Pago::MESES[$m])->join(', '),
                    'total_debe' => $pagos->sum('saldo'),
                ];
            })->sortByDesc('total_debe')->values();

        $totales = [
            'facturado' => $pagosDelAnio->sum('monto_total'),
            'cobrado' => $pagosDelAnio->sum(fn ($p) => $p->monto_total - $p->saldo),
            'pendiente' => $pagosDelAnio->sum('saldo'),
        ];

        return view('reportes.pagos-anual', compact('porMes', 'deudores', 'totales', 'anio'));
    }

    /** Reporte: planilla de pago a maestros. */
    public function planillaMaestros(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $data = Planilla::with(['maestro', 'alumno', 'especialidad'])
            ->where('mes', $mes)->where('anio', $anio)->orderBy('maestro_id')->get();

        return view('reportes.planilla-maestros', compact('data', 'mes', 'anio'));
    }

    /** Reporte: clases dictadas / canceladas en un rango. */
    public function clases(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->endOfMonth()->toDateString());

        $data = Clase::with(['alumno', 'maestro', 'especialidad'])
            ->whereDate('fecha', '>=', $desde)->whereDate('fecha', '<=', $hasta)
            ->orderBy('fecha')->get();

        $resumen = [
            'programadas' => $data->where('estado', 'programada')->count(),
            'realizadas' => $data->where('estado', 'realizada')->count(),
            'canceladas' => $data->where('estado', 'cancelada')->count(),
        ];

        return view('reportes.clases', compact('data', 'desde', 'hasta', 'resumen'));
    }
}
