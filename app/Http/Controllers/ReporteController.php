<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Asistencia;
use App\Models\CajaChica;
use App\Models\Clase;
use App\Models\Egreso;
use App\Models\Especialidad;
use App\Models\Maestro;
use App\Models\Pago;
use App\Models\Periodo;
use App\Models\Planilla;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * Reporte: alumnos por especialidad en un periodo. Cuenta talleres del
     * periodo (un alumno que lleva Piano y Canto suma en las dos), y cuantos
     * alumnos distintos hay en total.
     */
    public function alumnosPorEspecialidad(Request $request)
    {
        $periodos = Periodo::orderByDesc('anio')->orderByDesc('mes')->get();
        $periodo = $request->filled('periodo_id')
            ? $periodos->firstWhere('id', (int) $request->periodo_id)
            : Periodo::seleccionado();

        $talleres = $periodo
            ? AlumnoTaller::with(['especialidad', 'maestro'])->where('periodo_id', $periodo->id)->get()
            : collect();

        $data = $talleres->groupBy('especialidad_id')->map(fn ($ts) => [
            'especialidad' => $ts->first()->especialidad,
            'alumnos' => $ts->pluck('alumno_id')->unique()->count(),
            'maestros' => $ts->pluck('maestro.nombre')->filter()->unique()->sort()->values(),
        ])->sortByDesc('alumnos')->values();

        $totalAlumnos = $talleres->pluck('alumno_id')->unique()->count();

        return $this->responder($request, 'alumnos-especialidad', compact('data', 'periodos', 'periodo', 'totalAlumnos'), 'alumnos-por-especialidad-'.($periodo->nombre ?? ''));
    }

    /**
     * Reporte de asistencia mensual: por cada alumno con clases en el mes,
     * cuantas clases tuvo, a cuantas asistio, cuantas falto (con o sin aviso)
     * y cuantas aun no se marcan. Se puede filtrar por maestro.
     */
    public function asistenciaMensual(Request $request)
    {
        [$mesPeriodo, $anioPeriodo] = Periodo::mesAnioSeleccionado();
        $mes = (int) $request->get('mes', $mesPeriodo);
        $anio = (int) $request->get('anio', $anioPeriodo);
        $maestroId = $request->get('maestro_id');

        $clases = Clase::with(['alumno', 'maestro', 'especialidad', 'asistencia'])
            ->whereMonth('fecha', $mes)->whereYear('fecha', $anio)
            ->where('estado', '!=', 'cancelada')
            ->when($maestroId, fn ($q) => $q->where('maestro_id', $maestroId))
            ->get();

        $data = $clases->groupBy('alumno_id')->map(function ($cs) {
            $marcadas = $cs->filter(fn ($c) => $c->asistencia);
            $asistio = $marcadas->filter(fn ($c) => in_array($c->asistencia->estado, \App\Models\Asistencia::PRESENTES))->count();

            return [
                'alumno' => $cs->first()->alumno,
                'talleres' => $cs->map(fn ($c) => ($c->especialidad->nombre ?? '—').' · '.($c->maestro->nombre ?? '—'))->unique()->values(),
                'total' => $cs->count(),
                'asistio' => $asistio,
                'justificado' => $marcadas->filter(fn ($c) => $c->asistencia->estado === 'justificado')->count(),
                'faltas' => $marcadas->filter(fn ($c) => $c->asistencia->estado === 'falto')->count(),
                'sin_marcar' => $cs->count() - $marcadas->count(),
                'porcentaje' => $marcadas->count() > 0 ? round($asistio / $marcadas->count() * 100, 1) : null,
            ];
        })->sortBy(fn ($r) => $r['alumno']->nombre ?? '')->values();

        $marcadasTotal = $data->sum('asistio') + $data->sum('justificado') + $data->sum('faltas');
        $resumen = [
            'clases' => $data->sum('total'),
            'asistio' => $data->sum('asistio'),
            'faltas' => $data->sum('faltas') + $data->sum('justificado'),
            'sin_marcar' => $data->sum('sin_marcar'),
            'porcentaje' => $marcadasTotal > 0 ? round($data->sum('asistio') / $marcadasTotal * 100, 1) : null,
        ];

        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        return $this->responder($request, 'asistencia-mensual', compact('data', 'resumen', 'mes', 'anio', 'maestros', 'maestroId'), "asistencia-{$mes}-{$anio}", 'landscape');
    }

    /** Reporte: ingresos vs egresos por mes. */
    public function ingresosEgresos(Request $request)
    {
        $anio = $request->get('anio', Periodo::mesAnioSeleccionado()[1]);

        $data = collect(range(1, 12))->map(function ($mes) use ($anio) {
            // Ingreso = lo realmente cobrado (total del mes menos lo que aun se debe).
            $ingresos = Pago::where('mes', $mes)->where('anio', $anio)->sum(DB::raw('monto_total - saldo'));
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

        return $this->responder($request, 'ingresos-egresos', compact('data', 'anio'), "ingresos-egresos-{$anio}");
    }

    /**
     * Reporte: alumnos con pagos pendientes o parciales (seccion 14).
     * Filtros por periodo, maestro, taller (especialidad) y estado.
     */
    public function pagosPendientes(Request $request)
    {
        [$mesPeriodo, $anioPeriodo] = Periodo::mesAnioSeleccionado();
        $mes = $request->get('mes', $mesPeriodo);
        $anio = $request->get('anio', $anioPeriodo);

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

        return $this->responder($request, 'pagos-pendientes', compact('data', 'mes', 'anio', 'especialidades', 'maestros'), "pagos-pendientes-{$mes}-{$anio}", 'landscape');
    }

    /**
     * Reporte: historial de pagos de todo un anio (seccion 13). Permite
     * ver, mes a mes, cuanto se cobro, cuanto quedo pendiente y cuanto
     * esta "a cuenta", y quien debe.
     */
    public function pagosAnual(Request $request)
    {
        $anio = $request->get('anio', Periodo::mesAnioSeleccionado()[1]);

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
                    'meses_pendientes' => $pagos->pluck('mes')->unique()->sort()->map(fn ($m) => Pago::MESES[$m])->join(', '),
                    'total_debe' => $pagos->sum('saldo'),
                ];
            })->sortByDesc('total_debe')->values();

        $totales = [
            'facturado' => $pagosDelAnio->sum('monto_total'),
            'cobrado' => $pagosDelAnio->sum(fn ($p) => $p->monto_total - $p->saldo),
            'pendiente' => $pagosDelAnio->sum('saldo'),
        ];

        return $this->responder($request, 'pagos-anual', compact('porMes', 'deudores', 'totales', 'anio'), "pagos-{$anio}");
    }

    /** Reporte: planilla de pago a maestros. */
    public function planillaMaestros(Request $request)
    {
        [$mesPeriodo, $anioPeriodo] = Periodo::mesAnioSeleccionado();
        $mes = $request->get('mes', $mesPeriodo);
        $anio = $request->get('anio', $anioPeriodo);

        $data = Planilla::with(['maestro', 'alumno', 'especialidad'])
            ->where('mes', $mes)->where('anio', $anio)->orderBy('maestro_id')->get();

        return $this->responder($request, 'planilla-maestros', compact('data', 'mes', 'anio'), "planilla-{$mes}-{$anio}");
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

        return $this->responder($request, 'clases', compact('data', 'desde', 'hasta', 'resumen'), "clases-{$desde}-al-{$hasta}", 'landscape');
    }

    /**
     * Cada reporte se ve en pantalla o, con ?pdf=1 (mismos filtros), se
     * descarga en PDF. La vista PDF vive en reportes/pdf/{nombre}.
     */
    private function responder(Request $request, string $vista, array $datos, string $archivo, string $orientacion = 'portrait')
    {
        if (! $request->boolean('pdf')) {
            return view("reportes.{$vista}", $datos);
        }

        return Pdf::loadView("reportes.pdf.{$vista}", $datos)
            ->setPaper('a4', $orientacion)
            ->stream(\Illuminate\Support\Str::slug($archivo).'.pdf');
    }
}
