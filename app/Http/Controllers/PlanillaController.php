<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Especialidad;
use App\Models\Maestro;
use App\Models\Planilla;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanillaController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        // Agrupado por maestro (como en el Excel: un bloque por maestro,
        // con sus alumnos debajo y el total al final de cada bloque).
        $planillas = Planilla::with(['maestro', 'alumno', 'especialidad'])
            ->where('mes', $mes)->where('anio', $anio)
            ->get()
            ->groupBy('maestro_id')
            ->map(function ($items) {
                return [
                    'maestro' => $items->first()->maestro,
                    'items' => $items->sortBy(fn ($p) => $p->alumno->nombre ?? ''),
                    'total_horas' => $items->sum('horas'),
                    'total_monto' => $items->sum('monto'),
                ];
            })
            ->sortBy(fn ($g) => $g['maestro']->nombre ?? '');

        $maestros = Maestro::where('activo', true)->with('especialidades')->orderBy('nombre')->get();
        $alumnos = Alumno::activos()->orderBy('nombre')->get();
        $especialidades = Especialidad::orderBy('nombre')->get();
        $totalMes = Planilla::where('mes', $mes)->where('anio', $anio)->sum('monto');

        return view('planilla.index', compact('planillas', 'maestros', 'alumnos', 'especialidades', 'mes', 'anio', 'totalMes'));
    }

    /**
     * Genera/actualiza la planilla del mes a partir de las asistencias reales
     * de los alumnos (estado "asistio" o "tardanza"), usando la tarifa por
     * hora configurada para cada maestro+especialidad en su ficha.
     */
    public function generarDesdeAsistencia(Request $request)
    {
        $request->validate([
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|min:2020|max:2100',
        ]);

        $mes = (int) $request->mes;
        $anio = (int) $request->anio;

        $asistencias = Asistencia::with('clase')
            ->whereIn('estado', ['asistio', 'tardanza'])
            ->whereHas('clase', function ($q) use ($mes, $anio) {
                $q->whereMonth('fecha', $mes)->whereYear('fecha', $anio)
                    ->whereNotNull('maestro_id');
            })
            ->get()
            ->filter(fn ($a) => $a->clase && $a->clase->maestro_id);

        if ($asistencias->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay asistencias registradas para ese periodo. Marca la asistencia de las clases primero.',
            ], 422);
        }

        $grupos = $asistencias->groupBy(fn ($a) => $a->clase->maestro_id.'-'.$a->alumno_id.'-'.$a->clase->especialidad_id);

        $generados = 0;

        DB::transaction(function () use ($grupos, $mes, $anio, &$generados) {
            foreach ($grupos as $items) {
                $clase = $items->first()->clase;
                $maestro = Maestro::with('especialidades')->find($clase->maestro_id);
                if (! $maestro) {
                    continue;
                }

                $horas = round($items->sum(function ($a) {
                    $ini = Carbon::parse($a->clase->hora_inicio);
                    $fin = Carbon::parse($a->clase->hora_fin);

                    return $fin->diffInMinutes($ini) / 60;
                }), 1);

                $tarifa = $clase->especialidad_id ? $maestro->tarifaHoraPara($clase->especialidad_id) : 0;
                $monto = round($horas * $tarifa, 2);

                Planilla::updateOrCreate(
                    [
                        'maestro_id' => $clase->maestro_id,
                        'alumno_id' => $items->first()->alumno_id,
                        'especialidad_id' => $clase->especialidad_id,
                        'mes' => $mes,
                        'anio' => $anio,
                    ],
                    ['horas' => $horas, 'monto' => $monto]
                );

                $generados++;
            }
        });

        return response()->json(['ok' => true, 'message' => "Planilla generada: {$generados} registros (maestro + alumno) calculados desde la asistencia."]);
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $planilla = Planilla::create($data);

        return response()->json(['ok' => true, 'message' => 'Pago de maestro registrado en planilla.', 'data' => $planilla]);
    }

    public function edit(Planilla $planilla)
    {
        return response()->json(['ok' => true, 'data' => $planilla]);
    }

    public function update(Request $request, Planilla $planilla)
    {
        $data = $this->validarDatos($request);
        $planilla->update($data);

        return response()->json(['ok' => true, 'message' => 'Registro de planilla actualizado.', 'data' => $planilla]);
    }

    public function destroy(Planilla $planilla)
    {
        $planilla->delete();

        return response()->json(['ok' => true, 'message' => 'Registro de planilla eliminado.']);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'maestro_id' => 'required|exists:maestros,id',
            'alumno_id' => 'nullable|exists:alumnos,id',
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|min:2020|max:2100',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'horas' => 'nullable|numeric|min:0',
            'monto' => 'required|numeric|min:0',
            'observacion' => 'nullable|string|max:255',
        ], [
            'maestro_id.required' => 'Selecciona un maestro.',
            'monto.required' => 'El monto a pagar es obligatorio.',
        ]);
    }
}
