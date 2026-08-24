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
    public function index()
    {
        $horarios = Horario::with(['alumno', 'maestro', 'especialidad'])
            ->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        $alumnos = Alumno::activos()->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        return view('horarios.index', compact('horarios', 'alumnos', 'maestros'));
    }

    /**
     * Vista mensual por alumno, dividida en 4 semanas del mes (como el cuadro
     * de AGOSTO en Excel). Usa las clases reales del calendario si ya se
     * generaron; si no, proyecta las fechas segun el dia de la semana del
     * horario configurado.
     */
    public function vistaMensual(Request $request)
    {
        $mes = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);

        $horariosPorAlumno = Horario::with(['alumno', 'maestro', 'especialidad'])
            ->where('activo', true)
            ->get()
            ->groupBy('alumno_id');

        $clasesPorAlumno = Clase::whereMonth('fecha', $mes)->whereYear('fecha', $anio)
            ->get()->groupBy('alumno_id');

        $inicioMes = Carbon::create($anio, $mes, 1);
        $finMes = $inicioMes->copy()->endOfMonth();

        $filas = [];

        foreach ($horariosPorAlumno as $alumnoId => $horariosAlumno) {
            $clasesAlumno = $clasesPorAlumno->get($alumnoId, collect());
            $semanas = [1 => [], 2 => [], 3 => [], 4 => []];

            if ($clasesAlumno->isNotEmpty()) {
                foreach ($clasesAlumno as $clase) {
                    $dia = (int) $clase->fecha->format('j');
                    $semana = min(4, intdiv($dia - 1, 7) + 1);
                    $semanas[$semana][] = ['dia' => $dia, 'estado' => $clase->estado];
                }
            } else {
                // Aun no se generaron clases este mes: proyectamos las fechas
                // esperadas segun el dia de la semana de cada horario activo.
                foreach ($horariosAlumno as $h) {
                    for ($f = $inicioMes->copy(); $f->lte($finMes); $f->addDay()) {
                        if ($f->isoWeekday() == $h->dia_semana) {
                            $dia = (int) $f->format('j');
                            $semana = min(4, intdiv($dia - 1, 7) + 1);
                            $semanas[$semana][] = ['dia' => $dia, 'estado' => 'proyectada'];
                        }
                    }
                }
            }

            foreach ($semanas as $s => $arr) {
                usort($semanas[$s], fn ($a, $b) => $a['dia'] <=> $b['dia']);
            }

            $primero = $horariosAlumno->first();
            $filas[] = [
                'alumno' => $primero->alumno,
                'maestro' => $primero->maestro,
                'especialidad' => $primero->especialidad,
                'horario_texto' => $horariosAlumno->map(fn ($h) => $h->diaLabel().' '.Carbon::parse($h->hora_inicio)->format('H:i'))->implode(' / '),
                'semanas' => $semanas,
            ];
        }

        usort($filas, fn ($a, $b) => strcmp($a['alumno']->nombre ?? '', $b['alumno']->nombre ?? ''));

        return view('horarios.mensual', compact('filas', 'mes', 'anio'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $alumno = Alumno::find($data['alumno_id']);
        $data['especialidad_id'] = $data['especialidad_id'] ?? $alumno->especialidad_id;

        $horario = Horario::create($data);

        return response()->json(['ok' => true, 'message' => 'Horario creado correctamente.', 'data' => $horario]);
    }

    public function edit(Horario $horario)
    {
        return response()->json(['ok' => true, 'data' => $horario]);
    }

    public function update(Request $request, Horario $horario)
    {
        $data = $this->validarDatos($request);
        $horario->update($data);

        return response()->json(['ok' => true, 'message' => 'Horario actualizado correctamente.', 'data' => $horario]);
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

        $horarios = Horario::where('activo', true)->get();
        $creadas = 0;

        DB::transaction(function () use ($horarios, $desde, $hasta, &$creadas) {
            for ($fecha = $desde->copy(); $fecha->lte($hasta); $fecha->addDay()) {
                foreach ($horarios as $horario) {
                    if ($fecha->isoWeekday() != $horario->dia_semana) {
                        continue;
                    }

                    $existe = Clase::where('horario_id', $horario->id)
                        ->whereDate('fecha', $fecha->toDateString())
                        ->exists();

                    if ($existe) {
                        continue;
                    }

                    Clase::create([
                        'horario_id' => $horario->id,
                        'alumno_id' => $horario->alumno_id,
                        'maestro_id' => $horario->maestro_id,
                        'especialidad_id' => $horario->especialidad_id,
                        'fecha' => $fecha->toDateString(),
                        'hora_inicio' => $horario->hora_inicio,
                        'hora_fin' => $horario->hora_fin,
                        'salon' => $horario->salon,
                        'estado' => 'programada',
                    ]);

                    $creadas++;
                }
            }
        });

        return response()->json(['ok' => true, 'message' => "Se generaron {$creadas} clases en el calendario."]);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'salon' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
        ], [
            'alumno_id.required' => 'Selecciona un alumno.',
            'dia_semana.required' => 'Selecciona el dia de la semana.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);
    }
}
