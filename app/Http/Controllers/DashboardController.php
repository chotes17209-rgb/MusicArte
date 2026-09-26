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

        // Todo lo del periodo con el que se trabaja (barra superior): alumnos
        // inscritos, asistencia y pagos pendientes. Solo cantidades, sin
        // montos (19.2: el inicio no muestra informacion financiera).
        $periodoActual = Periodo::seleccionado();
        [$mesP, $anioP] = Periodo::mesAnioSeleccionado();

        if ($periodoActual) {
            $kpis['alumnos_activos'] = AlumnoPeriodo::where('periodo_id', $periodoActual->id)->activos()->count();
        }

        $asistenciasMes = \App\Models\Asistencia::whereHas('clase', function ($q) use ($periodoActual, $mesP, $anioP) {
            $periodoActual
                ? $q->where('periodo_id', $periodoActual->id)
                : $q->whereMonth('fecha', $mesP)->whereYear('fecha', $anioP);
        })->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $marcadas = $asistenciasMes->sum();
        $kpis['asistencia_mes'] = $marcadas > 0 ? round((($asistenciasMes['asistio'] ?? 0) + ($asistenciasMes['tardanza'] ?? 0)) / $marcadas * 100) : null;
        $kpis['alumnos_con_saldo'] = Alumno::whereHas('pagos', fn ($q) => $q->where('mes', $mesP)->where('anio', $anioP)->where('saldo', '>', 0))->count();

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

        $alumnosConSaldo = Alumno::query()
            ->whereHas('pagos', function ($q) use ($mesP, $anioP) {
                $q->where('mes', $mesP)->where('anio', $anioP)->where('saldo', '>', 0);
            })
            ->with(['pagos' => function ($q) use ($mesP, $anioP) {
                $q->where('mes', $mesP)->where('anio', $anioP);
            }])
            ->orderBy('nombre')
            ->limit(10)
            ->get();

        // Cumpleanos del mes (alerta simpatica para recepcion)
        // EXTRACT(DAY FROM ...) es compatible con PostgreSQL y MySQL moderno.
        $cumpleanieros = Alumno::activos()
            ->whereNotNull('fecha_nacimiento')
            ->whereMonth('fecha_nacimiento', now()->month)
            ->orderByRaw('EXTRACT(DAY FROM fecha_nacimiento)')
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
            'kpis', 'periodoActual', 'clasesHoy', 'alumnosPorMes', 'alumnosConSaldo', 'cumpleanieros', 'ultimasClasesCanceladas', 'alertaSunat'
        ));
    }

    /**
     * Busqueda rapida de la barra superior: alumnos y maestros por nombre
     * (sin importar mayusculas ni tildes). Devuelve JSON para el menu.
     */
    public function buscar(\Illuminate\Http\Request $request)
    {
        $palabras = preg_split('/\s+/', mb_strtolower(\Illuminate\Support\Str::ascii(trim((string) $request->get('q')))), -1, PREG_SPLIT_NO_EMPTY);
        if (! $palabras) {
            return response()->json(['alumnos' => [], 'maestros' => []]);
        }

        $col = fn (string $c) => \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql'
            ? "translate(lower($c), 'áéíóúüñàèìòù', 'aeiouunaeiou')" : "lower($c)";
        $filtrar = function ($q) use ($palabras, $col) {
            foreach ($palabras as $p) {
                $q->whereRaw($col('nombre').' LIKE ?', ['%'.addcslashes($p, '%_\\').'%']);
            }
        };

        $alumnos = Alumno::with('especialidad')->where($filtrar)->orderByDesc('activo')->orderBy('nombre')->limit(8)->get()
            ->map(fn ($a) => [
                'nombre' => $a->nombre,
                'detalle' => trim(($a->especialidad->nombre ?? '').($a->activo ? '' : ' · Inactivo'), ' ·'),
                'url' => route('alumnos.show', $a),
            ]);
        $maestros = Maestro::where($filtrar)->orderBy('nombre')->limit(4)->get()
            ->map(fn ($m) => ['nombre' => $m->nombre, 'detalle' => 'Maestro', 'url' => route('maestros.show', $m)]);

        return response()->json(['alumnos' => $alumnos, 'maestros' => $maestros]);
    }
}
