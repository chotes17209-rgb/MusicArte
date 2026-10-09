<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Horario;
use App\Models\Maestro;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    public function index(Request $request)
    {
        $periodoId = $request->get('periodo_id')
            ?? \App\Models\Periodo::seleccionado()?->id;

        $horarios = Horario::with(['alumno', 'maestro', 'especialidad', 'periodo'])
            ->when($periodoId, fn ($q) => $q->where('periodo_id', $periodoId))
            ->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        $alumnos = Alumno::activos()->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();
        $especialidades = \App\Models\Especialidad::where('activo', true)->orderBy('nombre')->get();
        $periodos = \App\Models\Periodo::orderByDesc('anio')->orderByDesc('mes')->get();

        return view('horarios.index', compact('horarios', 'alumnos', 'maestros', 'especialidades', 'periodos', 'periodoId'));
    }

    /**
     * "Tablero de horarios": el equivalente digital del cuadro fisico que
     * se pegaba en cada salon (maestro, dia y hora en una grilla). Un
     * bloque por cada maestro activo, para el periodo seleccionado, ya que
     * los horarios y hasta el maestro asignado pueden variar de un mes a
     * otro (por eso siempre se elige el periodo primero).
     */
    public function tablero(Request $request)
    {
        return view('horarios.tablero', $this->datosTablero($request));
    }

    /** El mismo cuadro en PDF (A4 horizontal) para imprimir y pegar en el salon. */
    public function tableroPdf(Request $request)
    {
        $datos = $this->datosTablero($request);
        abort_unless($datos['periodo'], 404);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('horarios.tablero-pdf', $datos)
            ->setPaper('a4', 'landscape')
            ->stream('horarios-'.\Illuminate\Support\Str::slug($datos['periodo']->nombre).'.pdf');
    }

    /**
     * Datos del cuadro de horarios: un bloque por maestro con clases en el
     * periodo (el de la barra superior por defecto), filtrable por maestro.
     */
    private function datosTablero(Request $request): array
    {
        $periodoId = $request->get('periodo_id') ?: \App\Models\Periodo::seleccionado()?->id;
        $periodo = $periodoId ? \App\Models\Periodo::find($periodoId) : null;
        $periodos = \App\Models\Periodo::orderByDesc('anio')->orderByDesc('mes')->get();
        $maestroFiltroId = $request->get('maestro_id');
        $todosMaestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        $horariosPorMaestro = $periodo
            ? Horario::with(['alumno', 'especialidad'])
                ->where('periodo_id', $periodo->id)->where('activo', true)
                ->when($maestroFiltroId, fn ($q) => $q->where('maestro_id', $maestroFiltroId))
                ->orderBy('dia_semana')->orderBy('hora_inicio')->get()
                ->groupBy('maestro_id')
            : collect();

        $maestros = $todosMaestros->filter(fn ($m) => $horariosPorMaestro->has($m->id))->values();
        $sinHorario = $maestroFiltroId ? collect() : $todosMaestros->reject(fn ($m) => $horariosPorMaestro->has($m->id))->values();

        return compact('maestros', 'todosMaestros', 'horariosPorMaestro', 'periodo', 'periodos', 'maestroFiltroId', 'sinHorario');
    }

    /**
     * Vista mensual por alumno, dividida en 4 semanas del mes (como el cuadro
     * de AGOSTO en Excel). Usa las clases reales del calendario si ya se
     * generaron; si no, proyecta las fechas segun el dia de la semana del
     * horario configurado.
     */
    public function vistaMensual(Request $request)
    {
        [$mesPeriodo, $anioPeriodo] = \App\Models\Periodo::mesAnioSeleccionado();
        $mes = (int) $request->get('mes', $mesPeriodo);
        $anio = (int) $request->get('anio', $anioPeriodo);

        // Solo los horarios del periodo de ese mes (antes se mezclaban los
        // de todos los periodos y un alumno aparecia con sus dias repetidos).
        $periodo = \App\Models\Periodo::where('mes', $mes)->where('anio', $anio)->first();

        $horarios = Horario::with(['alumno', 'maestro', 'especialidad'])
            ->where('activo', true)
            ->when($periodo, fn ($q) => $q->where('periodo_id', $periodo->id), fn ($q) => $q->whereNull('periodo_id'))
            ->orderBy('dia_semana')->orderBy('hora_inicio')
            ->get();

        // Una fila por taller (alumno + especialidad + maestro).
        $grupos = $horarios->groupBy(fn ($h) => $h->alumno_taller_id
            ? 't'.$h->alumno_taller_id
            : 'a'.$h->alumno_id.'-'.$h->especialidad_id.'-'.$h->maestro_id);

        $clasesPorHorario = Clase::whereMonth('fecha', $mes)->whereYear('fecha', $anio)
            ->whereIn('horario_id', $horarios->pluck('id'))
            ->get()->groupBy('horario_id');

        $inicioMes = Carbon::create($anio, $mes, 1);
        $finMes = $inicioMes->copy()->endOfMonth();
        $diasCortos = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

        $filas = [];
        foreach ($grupos as $horariosTaller) {
            $semanas = [1 => [], 2 => [], 3 => [], 4 => []];
            $clases = $horariosTaller->flatMap(fn ($h) => $clasesPorHorario->get($h->id, collect()));

            if ($clases->isNotEmpty()) {
                foreach ($clases as $clase) {
                    $dia = (int) $clase->fecha->format('j');
                    $semanas[min(4, intdiv($dia - 1, 7) + 1)][$dia] = ['dia' => $dia, 'estado' => $clase->estado];
                }
            } else {
                // Aun no se generaron clases este mes: se proyectan segun el dia de la semana.
                foreach ($horariosTaller as $h) {
                    for ($f = $inicioMes->copy(); $f->lte($finMes); $f->addDay()) {
                        if ($f->isoWeekday() == $h->dia_semana) {
                            $dia = (int) $f->format('j');
                            $semanas[min(4, intdiv($dia - 1, 7) + 1)][$dia] = ['dia' => $dia, 'estado' => 'proyectada'];
                        }
                    }
                }
            }
            foreach ($semanas as $s => $arr) {
                ksort($arr);
                $semanas[$s] = array_values($arr);
            }

            // "Lun · Mié · Vie  17:00" (agrupando los dias que comparten hora).
            $horarioTexto = $horariosTaller
                ->groupBy(fn ($h) => Carbon::parse($h->hora_inicio)->format('H:i').'-'.Carbon::parse($h->hora_fin)->format('H:i'))
                ->map(fn ($hs, $rango) => [
                    'dias' => $hs->pluck('dia_semana')->unique()->sort()->map(fn ($d) => $diasCortos[$d] ?? '')->implode(' · '),
                    'hora' => str_replace('-', ' – ', $rango),
                ])->values();

            $primero = $horariosTaller->first();
            $filas[] = [
                'alumno' => $primero->alumno,
                'maestro' => $primero->maestro,
                'especialidad' => $primero->especialidad,
                'horario' => $horarioTexto,
                'semanas' => $semanas,
                'total' => collect($semanas)->flatten(1)->count(),
            ];
        }

        usort($filas, fn ($a, $b) => strcmp($a['alumno']->nombre ?? '', $b['alumno']->nombre ?? ''));

        return view('horarios.mensual', compact('filas', 'mes', 'anio', 'periodo'));
    }

    public function show(Horario $horario)
    {
        $horario->load(['alumno', 'maestro', 'especialidad', 'periodo']);
        $clases = $horario->clases()->with('asistencia')->orderBy('fecha')->get();

        return view('horarios.show', compact('horario', 'clases'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $alumno = Alumno::find($data['alumno_id']);
        $data['especialidad_id'] = $data['especialidad_id'] ?? $alumno->especialidad_id;

        $horario = Horario::create($data);
        $horario->generarClases();

        return response()->json(['ok' => true, 'message' => 'Horario creado correctamente.', 'data' => $horario]);
    }

    public function edit(Horario $horario)
    {
        return response()->json(['ok' => true, 'data' => $horario]);
    }

    public function update(Request $request, Horario $horario)
    {
        $data = $this->validarDatos($request);
        // Al guardar, sus clases pendientes se ajustan solas (ver Horario::booted).
        $horario->update($data);

        return response()->json(['ok' => true, 'message' => 'Horario actualizado. Sus próximas clases ya se ajustaron.', 'data' => $horario]);
    }

    public function destroy(Horario $horario)
    {
        $horario->delete();

        return response()->json(['ok' => true, 'message' => 'Horario eliminado.']);
    }

    /**
     * Genera las clases concretas en el calendario a partir de la plantilla
     * de horarios, para un rango de fechas (por defecto: proximas 4 semanas).
     */
    public function generarClases(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = Carbon::parse($request->desde);
        $hasta = Carbon::parse($request->hasta);

        if ($desde->diffInDays($hasta) > 90) {
            return response()->json(['ok' => false, 'message' => 'El rango maximo permitido es de 90 dias.'], 422);
        }

        // Cada horario genera clases solo dentro de SU periodo (antes se usaban
        // los horarios de todos los meses y aparecian alumnos en dias que ya no
        // les tocaban). Solo talleres activos.
        $horarios = Horario::where('activo', true)
            ->whereHas('periodo', fn ($q) => $q->whereDate('fecha_inicio', '<=', $hasta->toDateString())
                ->whereDate('fecha_fin', '>=', $desde->toDateString()))
            ->where(fn ($q) => $q->whereNull('alumno_taller_id')
                ->orWhereHas('alumnoTaller', fn ($t) => $t->where('estado', 'activo')))
            ->get();

        $creadas = DB::transaction(fn () => $horarios->sum(fn (Horario $h) => $h->generarClases($desde, $hasta)));

        return response()->json(['ok' => true, 'message' => "Se generaron {$creadas} clases en el calendario."]);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'periodo_id' => 'required|exists:periodos,id',
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'salon' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
        ], [
            'alumno_id.required' => 'Selecciona un alumno.',
            'periodo_id.required' => 'Selecciona el periodo al que pertenece este horario.',
            'dia_semana.required' => 'Selecciona el dia de la semana.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);
    }
}
