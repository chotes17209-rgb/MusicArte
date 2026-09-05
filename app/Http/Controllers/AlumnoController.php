<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Maestro;
use App\Models\Periodo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumno::with(['especialidad', 'maestro']);

        // 1.3 Busqueda automatica/reactiva: nombre, dni o tutor.
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('dni', 'like', "%{$buscar}%")
                    ->orWhere('tutor', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('especialidad_id')) {
            $query->where('especialidad_id', $request->especialidad_id);
        }

        // 1.2 Filtro por maestro.
        if ($request->filled('maestro_id')) {
            $query->where('maestro_id', $request->maestro_id);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        // 1.1 Filtro por periodo/mes: se considera "del periodo" al alumno que
        // tiene al menos un horario/clase programada dentro de ese periodo.
        // NOTA: en la Fase 3 (gestion de periodos) se creara una tabla pivote
        // alumno_periodo para poder marcar activo/inactivo por mes de forma
        // independiente del horario, lo cual hara este filtro mas preciso.
        if ($request->filled('periodo_id')) {
            $periodoId = $request->periodo_id;
            $query->whereHas('horarios', function ($q) use ($periodoId) {
                $q->where('periodo_id', $periodoId);
            });
        }

        $alumnos = $query->orderBy('nombre')->paginate(15)->withQueryString();

        $especialidades = Especialidad::where('activo', true)->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();
        $periodos = Periodo::where('activo', true)->orderByDesc('anio')->orderByDesc('mes')->get();

        // Peticion AJAX (busqueda/filtros reactivos): solo devolvemos el
        // fragmento de la tabla, sin recargar toda la pagina.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'html' => view('alumnos._tabla', compact('alumnos'))->render(),
            ]);
        }

        return view('alumnos.index', compact('alumnos', 'especialidades', 'maestros', 'periodos'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $alumno = Alumno::create($data);

        $mensajeHorario = $this->programarHorario($request, $alumno);

        return response()->json(['ok' => true, 'message' => "Alumno '{$alumno->nombre}' registrado correctamente.".$mensajeHorario, 'data' => $alumno]);
    }

    public function edit(Alumno $alumno)
    {
        $alumno->load(['horarios' => fn ($q) => $q->where('activo', true)]);

        return response()->json(['ok' => true, 'data' => $alumno]);
    }

    public function update(Request $request, Alumno $alumno)
    {
        $data = $this->validarDatos($request);
        $alumno->update($data);

        $mensajeHorario = $this->programarHorario($request, $alumno);

        return response()->json(['ok' => true, 'message' => 'Datos del alumno actualizados.'.$mensajeHorario, 'data' => $alumno]);
    }

    /**
     * Si el formulario trae periodo + un horario por dia, crea/actualiza el
     * Horario (plantilla semanal) de cada dia del alumno -con su propia hora-
     * y genera automaticamente las Clases en el calendario para todo el rango
     * de fechas del periodo. No hace falta tocar el calendario a mano.
     */
    private function programarHorario(Request $request, Alumno $alumno): string
    {
        if (empty($request->input('horarios'))) {
            return '';
        }

        $data = $request->validate([
            'periodo_id' => 'required|exists:periodos,id',
            'horarios' => 'required|array|min:1',
            'horarios.*.dia_semana' => 'required|integer|between:1,7',
            'horarios.*.hora_inicio' => 'required',
            'horarios.*.hora_fin' => 'required',
        ], [
            'periodo_id.required' => 'Selecciona el periodo para programar las clases.',
            'horarios.required' => 'Selecciona al menos un dia de clase.',
            'horarios.*.hora_inicio.required' => 'Falta la hora de inicio en uno de los dias marcados.',
            'horarios.*.hora_fin.required' => 'Falta la hora de fin en uno de los dias marcados.',
        ]);

        // Validamos a mano que la hora de fin sea posterior a la de inicio, dia por dia.
        foreach ($data['horarios'] as $i => $h) {
            if (strtotime($h['hora_fin']) <= strtotime($h['hora_inicio'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "horarios.$i.hora_fin" => 'La hora de fin debe ser posterior a la hora de inicio ('.Horario::DIAS[$h['dia_semana']].').',
                ]);
            }
        }

        $periodo = Periodo::findOrFail($data['periodo_id']);
        $creadas = 0;

        DB::transaction(function () use ($alumno, $periodo, $data, &$creadas) {
            foreach ($data['horarios'] as $h) {
                $horario = Horario::updateOrCreate(
                    ['alumno_id' => $alumno->id, 'dia_semana' => $h['dia_semana']],
                    [
                        'maestro_id' => $alumno->maestro_id,
                        'especialidad_id' => $alumno->especialidad_id,
                        'periodo_id' => $periodo->id,
                        'hora_inicio' => $h['hora_inicio'],
                        'hora_fin' => $h['hora_fin'],
                        // El salon ahora se gestiona desde el modulo de
                        // Horarios/Talleres (Fase 2), ya no desde el
                        // formulario de registro de alumnos.
                        'activo' => true,
                    ]
                );

                for ($fecha = $periodo->fecha_inicio->copy(); $fecha->lte($periodo->fecha_fin); $fecha->addDay()) {
                    if ($fecha->isoWeekday() != $h['dia_semana']) {
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
                        'alumno_id' => $alumno->id,
                        'maestro_id' => $horario->maestro_id,
                        'especialidad_id' => $horario->especialidad_id,
                        'periodo_id' => $periodo->id,
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

        return " Se generaron {$creadas} clases en el calendario para el periodo {$periodo->nombre}.";
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->delete();

        return response()->json(['ok' => true, 'message' => 'Alumno eliminado.']);
    }

    private function validarDatos(Request $request): array
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'tutor' => 'nullable|string|max:150',
            'celular' => 'nullable|string|max:20',
            'dni' => 'nullable|string|max:20',
            'diagnostico' => 'nullable|string',
            'fecha_ingreso' => 'nullable|date',
            'activo' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre del alumno es obligatorio.',
        ]);

        // 2.1 Edad automatica: se calcula en el servidor a partir de la
        // fecha de nacimiento, nunca se toma un valor de edad enviado por
        // el usuario. Si no hay fecha de nacimiento, no hay edad.
        $data['edad'] = $this->calcularEdad($data['fecha_nacimiento'] ?? null);

        // 2.2 Ya no se solicita direccion en el formulario. No se incluye en
        // $data para no sobrescribir con vacio un registro ya existente; el
        // campo se mantiene en la base de datos solo por compatibilidad con
        // datos historicos, pero deja de usarse en la aplicacion.
        return $data;
    }

    private function calcularEdad(?string $fechaNacimiento): ?int
    {
        if (! $fechaNacimiento) {
            return null;
        }

        return Carbon::parse($fechaNacimiento)->age;
    }
}