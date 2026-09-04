<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Maestro;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $alumnos = $this->alumnosFiltrados($request);

        $especialidades = Especialidad::where('activo', true)->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('anio')->orderByDesc('mes')->get();

        // Busqueda/filtrado reactivo (req. 1.3): si la peticion viene por AJAX
        // (fetch desde el JS de la vista), devolvemos solo el HTML de la tabla
        // para reemplazarlo sin recargar la pagina completa.
        if ($request->ajax() || $request->wantsJson()) {
            return view('alumnos._tabla', compact('alumnos'))->render();
        }

        return view('alumnos.index', compact('alumnos', 'especialidades', 'maestros', 'periodos'));
    }

    /**
     * Filtros combinables (req. 1.1, 1.2, 1.3):
     *  - buscar: nombre, DNI o tutor (reactivo, sin boton "Buscar").
     *  - periodo_id: solo alumnos con algun taller en ese periodo.
     *  - maestro_id: solo alumnos con algun taller de ese maestro.
     *  - especialidad_id / estado: filtros que ya existian.
     * El periodo y el maestro se pueden combinar entre si (ej. periodo=agosto
     * 2026 + maestro=Juan Perez -> alumnos activos de agosto con Juan Perez).
     */
    private function alumnosFiltrados(Request $request)
    {
        $query = Alumno::with(['especialidad', 'maestro', 'horarios.especialidad', 'horarios.maestro', 'horarios.periodo']);

        if ($request->filled('buscar')) {
            $texto = $request->buscar;
            $query->where(function ($q) use ($texto) {
                $q->where('nombre', 'like', "%{$texto}%")
                    ->orWhere('dni', 'like', "%{$texto}%")
                    ->orWhere('tutor', 'like', "%{$texto}%");
            });
        }

        if ($request->filled('especialidad_id')) {
            $especialidadId = $request->especialidad_id;
            $query->where(function ($q) use ($especialidadId) {
                $q->where('especialidad_id', $especialidadId)
                    ->orWhereHas('horarios', fn ($h) => $h->where('especialidad_id', $especialidadId));
            });
        }

        if ($request->filled('maestro_id')) {
            $query->delMaestro((int) $request->maestro_id);
        }

        if ($request->filled('periodo_id')) {
            $query->delPeriodo((int) $request->periodo_id);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        return $query->orderBy('nombre')->paginate(15)->withQueryString();
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $alumno = Alumno::create($data);

        $mensajeHorario = $this->programarTalleres($request, $alumno);

        return response()->json(['ok' => true, 'message' => "Alumno '{$alumno->nombre}' registrado correctamente.".$mensajeHorario, 'data' => $alumno]);
    }

    public function edit(Alumno $alumno)
    {
        $alumno->load(['horarios' => fn ($q) => $q->orderBy('dia_semana')]);
        $alumno->setAttribute('talleres', $alumno->talleres());

        return response()->json(['ok' => true, 'data' => $alumno]);
    }

    public function update(Request $request, Alumno $alumno)
    {
        $data = $this->validarDatos($request);
        $alumno->update($data);

        $mensajeHorario = $this->programarTalleres($request, $alumno);

        return response()->json(['ok' => true, 'message' => 'Datos del alumno actualizados.'.$mensajeHorario, 'data' => $alumno]);
    }

    /**
     * Un alumno puede tener VARIOS talleres a la vez (req. 3 y 4). El
     * formulario envia un arreglo "talleres", cada uno con su propia
     * especialidad, maestro, periodo, salon y dias/horas. Por cada taller se
     * crean/actualizan sus Horarios y se generan las Clases del periodo
     * correspondiente, igual que antes, pero ya no se asume un unico taller
     * por alumno.
     */
    private function programarTalleres(Request $request, Alumno $alumno): string
    {
        if (empty($request->input('talleres'))) {
            return '';
        }

        $data = $request->validate([
            'talleres' => 'required|array|min:1',
            'talleres.*.especialidad_id' => 'required|exists:especialidades,id',
            'talleres.*.maestro_id' => 'nullable|exists:maestros,id',
            'talleres.*.periodo_id' => 'required|exists:periodos,id',
            'talleres.*.salon' => 'nullable|string|max:50',
            'talleres.*.horarios' => 'required|array|min:1',
            'talleres.*.horarios.*.dia_semana' => 'required|integer|between:1,7',
            'talleres.*.horarios.*.hora_inicio' => 'required',
            'talleres.*.horarios.*.hora_fin' => 'required',
        ], [
            'talleres.*.especialidad_id.required' => 'Cada taller necesita una especialidad.',
            'talleres.*.periodo_id.required' => 'Selecciona el periodo de cada taller.',
            'talleres.*.horarios.required' => 'Selecciona al menos un dia de clase por taller.',
        ]);

        foreach ($data['talleres'] as $ti => $taller) {
            foreach ($taller['horarios'] as $hi => $h) {
                if (strtotime($h['hora_fin']) <= strtotime($h['hora_inicio'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "talleres.$ti.horarios.$hi.hora_fin" => 'La hora de fin debe ser posterior a la de inicio ('.Horario::DIAS[$h['dia_semana']].').',
                    ]);
                }
            }
        }

        $creadas = 0;
        $nombresTalleres = [];

        DB::transaction(function () use ($alumno, $data, &$creadas, &$nombresTalleres) {
            foreach ($data['talleres'] as $taller) {
                $periodo = Periodo::findOrFail($taller['periodo_id']);
                $especialidad = Especialidad::find($taller['especialidad_id']);
                $nombresTalleres[] = $especialidad->nombre ?? 'Taller';

                foreach ($taller['horarios'] as $h) {
                    $horario = Horario::updateOrCreate(
                        [
                            'alumno_id' => $alumno->id,
                            'especialidad_id' => $taller['especialidad_id'],
                            'maestro_id' => $taller['maestro_id'] ?? null,
                            'periodo_id' => $periodo->id,
                            'dia_semana' => $h['dia_semana'],
                        ],
                        [
                            'hora_inicio' => $h['hora_inicio'],
                            'hora_fin' => $h['hora_fin'],
                            'salon' => $taller['salon'] ?? null,
                            'activo' => true,
                            'estado' => 'activo',
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
            }

            // Compatibilidad con modulos que aun leen alumno->especialidad/maestro
            // directamente (reportes, dashboard): se guarda el PRIMER taller como
            // "principal", pero el listado real de talleres sale de horarios().
            $primero = $data['talleres'][0];
            $alumno->forceFill([
                'especialidad_id' => $primero['especialidad_id'],
                'maestro_id' => $primero['maestro_id'] ?? null,
            ])->save();
        });

        return ' Se programaron '.count($nombresTalleres).' taller(es) ('.implode(', ', array_unique($nombresTalleres)).') y se generaron '.$creadas.' clases en el calendario.';
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->delete();

        return response()->json(['ok' => true, 'message' => 'Alumno eliminado.']);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
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
    }
}
