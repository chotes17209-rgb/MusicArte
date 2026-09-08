<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Especialidad;
use App\Models\Maestro;
use App\Models\Periodo;
use App\Services\HorarioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->aplicarFiltros($request);

        $alumnos = $query->orderBy('nombre')->paginate(15)->withQueryString();

        $especialidades = Especialidad::where('activo', true)->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();
        $periodos = Periodo::where('activo', true)->orderByDesc('anio')->orderByDesc('mes')->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'html' => view('alumnos._tabla', compact('alumnos'))->render(),
            ]);
        }

        return view('alumnos.index', compact('alumnos', 'especialidades', 'maestros', 'periodos'));
    }

    /**
     * "Ver" alumno (CRUD completo: la R de "Read"). Muestra el perfil
     * completo y, sobre todo, la LINEA DE TIEMPO por periodo: con que
     * maestro/taller/horario estuvo en cada mes. Se arma a partir de los
     * horarios reales (Horario), que nunca se sobreescriben entre periodos
     * (ver HorarioService), asi que es la fuente mas confiable para
     * responder "con que maestro estuvo en enero" sin importar si el
     * taller se edito o se creo uno nuevo para reinscribirlo.
     */
    public function show(Alumno $alumno)
    {
        $alumno->load(['especialidad', 'maestro']);

        $horariosPorPeriodo = \App\Models\Horario::with(['maestro', 'especialidad', 'periodo'])
            ->where('alumno_id', $alumno->id)
            ->get()
            ->groupBy('periodo_id');

        $estadosPorPeriodo = $alumno->historialPeriodos()->pluck('estado', 'periodo_id');

        $idsPeriodos = $horariosPorPeriodo->keys()
            ->merge($estadosPorPeriodo->keys())
            ->merge($alumno->talleres()->pluck('periodo_id'))
            ->filter()->unique();

        $periodos = Periodo::whereIn('id', $idsPeriodos)->orderByDesc('anio')->orderByDesc('mes')->get();

        $lineaDeTiempo = $periodos->map(function ($periodo) use ($horariosPorPeriodo, $estadosPorPeriodo, $alumno) {
            $horarios = $horariosPorPeriodo->get($periodo->id, collect());
            $talleresDelPeriodo = $alumno->talleres()->where('periodo_id', $periodo->id)
                ->with(['especialidad', 'maestro'])->get();

            return [
                'periodo' => $periodo,
                'estado' => $estadosPorPeriodo->get($periodo->id, $horarios->isNotEmpty() ? 'activo' : null),
                'horarios' => $horarios->sortBy('dia_semana'),
                'talleres' => $talleresDelPeriodo,
            ];
        });

        $pagos = $alumno->pagos()->orderByDesc('anio')->orderByDesc('mes')->limit(12)->get();
        $tallerActual = $alumno->talleres()->where('estado', 'activo')->with(['especialidad', 'maestro', 'periodo'])->get();

        return view('alumnos.show', compact('alumno', 'lineaDeTiempo', 'pagos', 'tallerActual'));
    }

    /**
     * Seccion 18: imprimir la lista de alumnos respetando los mismos
     * filtros (periodo, maestro, especialidad, estado, busqueda) que se
     * tenian aplicados en la pantalla de alumnos.
     */
    public function imprimir(Request $request)
    {
        $alumnos = $this->aplicarFiltros($request)->orderBy('nombre')->get();

        $especialidad = $request->filled('especialidad_id') ? Especialidad::find($request->especialidad_id) : null;
        $maestro = $request->filled('maestro_id') ? Maestro::find($request->maestro_id) : null;
        $periodo = $request->filled('periodo_id') ? Periodo::find($request->periodo_id) : null;

        return view('alumnos.imprimir', compact('alumnos', 'especialidad', 'maestro', 'periodo'));
    }

    private function aplicarFiltros(Request $request)
    {
        $query = Alumno::with(['especialidad', 'maestro'])
            ->withCount(['talleres as talleres_activos_count' => fn ($q) => $q->where('estado', 'activo')]);

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

        // 1.2 Filtro por maestro: revisa tanto el "taller principal" (legado)
        // como cualquier taller activo del alumno, para cubrir el caso de
        // alumnos con varios talleres y distintos maestros.
        if ($request->filled('maestro_id')) {
            $maestroId = $request->maestro_id;
            $query->where(function ($q) use ($maestroId) {
                $q->where('maestro_id', $maestroId)
                    ->orWhereHas('talleres', function ($qt) use ($maestroId) {
                        $qt->where('maestro_id', $maestroId)->where('estado', 'activo');
                    });
            });
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        // 1.1 Filtro por periodo/mes: se considera "del periodo" al alumno
        // que tiene al menos un horario programado dentro de ese periodo
        // (en cualquiera de sus talleres).
        if ($request->filled('periodo_id')) {
            $periodoId = $request->periodo_id;
            $query->whereHas('horarios', function ($q) use ($periodoId) {
                $q->where('periodo_id', $periodoId);
            });
        }

        return $query;
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);

        $alumno = DB::transaction(function () use ($request, $data) {
            $alumno = Alumno::create($data);
            $this->crearTallerInicial($request, $alumno);

            return $alumno;
        });

        $alumno->sincronizarTallerPrincipal();

        return response()->json([
            'ok' => true,
            'message' => "Alumno '{$alumno->nombre}' registrado correctamente.",
            'data' => $alumno->load('talleres.especialidad', 'talleres.maestro'),
        ]);
    }

    public function edit(Alumno $alumno)
    {
        // Un alumno puede tener varios talleres (seccion 3): se cargan todos
        // (activos e inactivos) con su especialidad, maestro, periodo y su
        // propio horario, para poder gestionarlos desde el modal.
        $alumno->load([
            'talleres' => fn ($q) => $q->orderByDesc('estado')->orderBy('id'),
            'talleres.especialidad',
            'talleres.maestro',
            'talleres.periodo',
            'talleres.horarios' => fn ($q) => $q->where('activo', true),
        ]);

        return response()->json(['ok' => true, 'data' => $alumno]);
    }

    public function update(Request $request, Alumno $alumno)
    {
        $data = $this->validarDatos($request);
        $alumno->update($data);

        return response()->json(['ok' => true, 'message' => 'Datos del alumno actualizados.', 'data' => $alumno]);
    }

    public function destroy(Alumno $alumno)
    {
        // Al eliminar el alumno se eliminan en cascada sus talleres,
        // horarios y clases (relacion cascadeOnDelete en las migraciones).
        $alumno->delete();

        return response()->json(['ok' => true, 'message' => 'Alumno eliminado.']);
    }

    /**
     * Al registrar un alumno nuevo, opcionalmente se puede inscribir de una
     * vez en su primer taller (especialidad + maestro + periodo/horarios).
     * Para agregarle mas talleres despues, se usa AlumnoTallerController
     * desde la pantalla de edicion.
     */
    private function crearTallerInicial(Request $request, Alumno $alumno): void
    {
        if (! $request->filled('especialidad_id')) {
            return;
        }

        $data = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'periodo_id' => 'nullable|exists:periodos,id',
        ]);

        $taller = AlumnoTaller::create([
            'alumno_id' => $alumno->id,
            'especialidad_id' => $data['especialidad_id'],
            'maestro_id' => $data['maestro_id'] ?? null,
            'periodo_id' => $data['periodo_id'] ?? null,
            'estado' => 'activo',
        ]);

        if ($request->filled('horarios')) {
            HorarioService::generar($taller, $request->input('horarios'));
        }
    }

    private function validarDatos(Request $request): array
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'fecha_nacimiento' => 'nullable|date',
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