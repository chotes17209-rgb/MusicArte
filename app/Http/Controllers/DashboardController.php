<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Egreso;
use App\Models\Maestro;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = now()->toDateString();

        $kpis = [
            'alumnos_activos' => Alumno::activos()->count(),
            'maestros_activos' => Maestro::where('activo', true)->count(),
            'clases_hoy' => Clase::whereDate('fecha', $hoy)->count(),
            'clases_hoy_realizadas' => Clase::whereDate('fecha', $hoy)->where('estado', 'realizada')->count(),
            'ingresos_mes' => Pago::where('mes', now()->month)->where('anio', now()->year)->sum('monto_total'),
            'egresos_mes' => Egreso::whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('total'),
            'pagos_pendientes' => Pago::where('mes', now()->month)->where('anio', now()->year)->where('saldo', '>', 0)->count(),
            'saldo_pendiente_total' => Pago::where('mes', now()->month)->where('anio', now()->year)->sum('saldo'),
        ];

        $clasesHoy = Clase::with(['alumno', 'maestro', 'especialidad'])
            ->whereDate('fecha', $hoy)
            ->orderBy('hora_inicio')
            ->get();

        $alumnosConSaldo = Alumno::activos()
            ->whereHas('pagos', function ($q) {
                $q->where('mes', now()->month)->where('anio', now()->year)->where('saldo', '>', 0);
            })
            ->with(['pagos' => function ($q) {
                $q->where('mes', now()->month)->where('anio', now()->year);
            }])
            ->limit(8)
            ->get();

        // Cumpleanos del mes (alerta simpatica para recepcion)
        $cumpleanieros = Alumno::activos()
            ->whereNotNull('fecha_nacimiento')
            ->whereMonth('fecha_nacimiento', now()->month)
            ->orderByRaw('DAY(fecha_nacimiento)')
            ->get();

        $ultimasClasesCanceladas = Clase::with(['alumno'])
            ->where('estado', 'cancelada')
            ->whereDate('fecha', '>=', now()->subDays(7))
            ->latest('fecha')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'kpis', 'clasesHoy', 'alumnosConSaldo', 'cumpleanieros', 'ultimasClasesCanceladas'
        ));
    }
}
