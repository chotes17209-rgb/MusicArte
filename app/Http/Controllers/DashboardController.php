<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoPeriodo;
use App\Models\Clase;
use App\Models\Maestro;
use App\Models\Pago;
use App\Models\Periodo;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = now()->toDateString();

        // 19.2: el Dashboard principal ya NO muestra ingresos ni egresos;
        // esos datos financieros solo viven en el modulo de Pagos/Reportes.
        $kpis = [
            'alumnos_activos' => Alumno::activos()->count(),
            'maestros_activos' => Maestro::where('activo', true)->count(),
            'clases_hoy' => Clase::whereDate('fecha', $hoy)->count(),
            'clases_hoy_realizadas' => Clase::whereDate('fecha', $hoy)->where('estado', 'realizada')->count(),
        ];

        $clasesHoy = Clase::with(['alumno', 'maestro', 'especialidad'])
            ->whereDate('fecha', $hoy)
            ->orderBy('hora_inicio')
            ->get();

        // 19.1: cantidad de alumnos activos por periodo/mes (ultimos 6 periodos).
        $alumnosPorMes = Periodo::orderByDesc('anio')->orderByDesc('mes')->limit(6)->get()
            ->sortBy(fn ($p) => $p->anio * 100 + $p->mes)
            ->map(fn ($p) => [
                'label' => $p->nombre,
                'cantidad' => AlumnoPeriodo::where('periodo_id', $p->id)->activos()->count(),
            ])->values();

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

        // 20. Alerta de SUNAT y servicios: la fecha objetivo es el dia 14 de
        // cada mes. Se calcula siempre en base a la fecha actual (nunca una
        // fecha fija), y sigue funcionando igual mes a mes.
        $hoyCarbon = now();
        $dia14 = $hoyCarbon->copy()->day(14)->startOfDay();
        if ($hoyCarbon->day > 14) {
            // Ya paso el 14 de este mes: la proxima alerta es el 14 del mes siguiente.
            $dia14 = $hoyCarbon->copy()->addMonthNoOverflow()->day(14)->startOfDay();
        }
        $diasParaSunat = (int) $hoyCarbon->copy()->startOfDay()->diffInDays($dia14, false);
        $alertaSunat = [
            'es_hoy' => $diasParaSunat === 0,
            'dias_restantes' => $diasParaSunat,
            'mostrar' => $diasParaSunat >= 0 && $diasParaSunat <= 5,
        ];

        return view('dashboard', compact(
            'kpis', 'clasesHoy', 'alumnosPorMes', 'alumnosConSaldo', 'cumpleanieros', 'ultimasClasesCanceladas', 'alertaSunat'
        ));
    }
}
