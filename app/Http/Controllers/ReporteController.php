<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\CajaChica;
use App\Models\Clase;
use App\Models\Egreso;
use App\Models\Especialidad;
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

    /** Reporte: asistencia mensual por alumno. */
    public function asistenciaMensual(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $data = Alumno::activos()->with(['asistencias' => function ($q) use ($mes, $anio) {
            $q->whereHas('clase', fn ($c) => $c->whereMonth('fecha', $mes)->whereYear('fecha', $anio));
        }])->get()->map(function ($alumno) {
            $total = $alumno->asistencias->count();
            $asistio = $alumno->asistencias->where('estado', 'asistio')->count();
            $faltas = $alumno->asistencias->where('estado', 'falto')->count();
            return [
                'alumno' => $alumno->nombre,
                'total' => $total,
                'asistio' => $asistio,
                'faltas' => $faltas,
                'porcentaje' => $total > 0 ? round($asistio / $total * 100, 1) : 0,
            ];
        });

        return view('reportes.asistencia-mensual', compact('data', 'mes', 'anio'));
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

    /** Reporte: alumnos con pago completo, parcial o sin pago (req. 14). */
    public function pagosPendientes(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $query = Pago::with(['alumno', 'especialidad', 'maestro'])
            ->where('mes', $mes)->where('anio', $anio);

        if ($request->filled('maestro_id')) {
            $query->where('maestro_id', $request->maestro_id);
        }
        if ($request->filled('especialidad_id')) {
            $query->where('especialidad_id', $request->especialidad_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            $query->where('estado', '!=', 'pagado');
        }

        $data = $query->orderByDesc('saldo')->get();
        $maestros = \App\Models\Maestro::where('activo', true)->orderBy('nombre')->get();
        $especialidades = Especialidad::where('activo', true)->orderBy('nombre')->get();

        return view('reportes.pagos-pendientes', compact('data', 'mes', 'anio', 'maestros', 'especialidades'));
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
