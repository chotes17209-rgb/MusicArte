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
use Illuminate\Support\Str;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        // Por defecto se muestran los alumnos del periodo con el que se
        // trabaja (barra superior). "Todos los periodos" envia periodo_id vacio.
        if (! $request->has('periodo_id')) {
            $request->merge(['periodo_id' => Periodo::seleccionado()?->id]);
        }

        $query = $this->aplicarFiltros($request);

        $alumnos = $query->orderBy('nombre')->paginate(15)->withQueryString();
        $estados = $this->estadosEnPeriodo($alumnos->getCollection(), $request->periodo_id);

        $especialidades = Especialidad::where('activo', true)->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();
        $periodos = Periodo::where('activo', true)->orderByDesc('anio')->orderByDesc('mes')->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'html' => view('alumnos._tabla', compact('alumnos', 'estados'))->render(),
            ]);
        }

        // Seccion "Periodos" (movida aqui dentro de Alumnos): a diferencia
        // de $periodos (solo activos, para los selects de taller), esta
        // lista trae TODOS los periodos, activos e inactivos, para el CRUD.
        $todosPeriodos = Periodo::orderByDesc('anio')->orderByDesc('mes')->get();

        return view('alumnos.index', compact('alumnos', 'estados', 'especialidades', 'maestros', 'periodos', 'todosPeriodos'));
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

    /**
     * Estado de cada alumno EN el periodo que se esta viendo (no su estado
     * general). En un periodo ya terminado se indica a que periodo paso o
     * que ahi termino; en uno vigente, si esta activo o no ese mes.
     *
     * @return array<int, array{texto: string, clase: string}> alumno_id => estado
     */
    private function estadosEnPeriodo($alumnos, $periodoId): array
    {
        $periodo = $periodoId ? Periodo::find($periodoId) : null;
        if (! $periodo || $alumnos->isEmpty()) {
            return [];
        }

        $ids = $alumnos->pluck('id');
        $inactivosEnPeriodo = \App\Models\AlumnoPeriodo::where('periodo_id', $periodo->id)
            ->whereIn('alumno_id', $ids)->where('estado', 'inactivo')->pluck('alumno_id')->flip();

        if (! $periodo->haTerminado()) {
            return $ids->mapWithKeys(fn ($id) => [$id => isset($inactivosEnPeriodo[$id])
                ? ['texto' => 'Inactivo este mes', 'clase' => 'bg-secondary']
                : ['texto' => 'Activo', 'clase' => 'bg-success']])->all();
        }

        $siguiente = $periodo->siguientePeriodoDe($ids);

        return $ids->mapWithKeys(fn ($id) => [$id => match (true) {
            isset($inactivosEnPeriodo[$id]) => ['texto' => 'No estudió este mes', 'clase' => 'bg-secondary'],
            $siguiente->has($id) => ['texto' => 'Pasó a '.$siguiente[$id], 'clase' => 'bg-info'],
            default => ['texto' => 'Terminó aquí', 'clase' => 'bg-warning'],
        }])->all();
    }

    private function aplicarFiltros(Request $request)
    {
        $query = Alumno::with(['especialidad', 'maestro'])
            ->withCount(['talleres as talleres_activos_count' => fn ($q) => $q->where('estado', 'activo')]);

        // 1.3 Busqueda automatica/reactiva: nombre, dni o tutor. Sin
        // distinguir mayusculas ni tildes ("aless" encuentra "Alessandro",
        // "jose" encuentra "José"), y cada palabra se busca por separado
        // ("aless aliaga" encuentra "Alessandro Aliaga Palomino").
        if ($request->filled('buscar')) {
            $normalizar = fn (string $col) => DB::getDriverName() === 'pgsql'
                ? "translate(lower($col), 'áéíóúüñàèìòù', 'aeiouunaeiou')"
                : "lower($col)";

            $palabras = preg_split('/\s+/', mb_strtolower(Str::ascii(trim($request->buscar))), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($palabras as $palabra) {
                $like = '%'.addcslashes($palabra, '%_\\').'%';
                $query->where(function ($q) use ($normalizar, $like) {
                    $q->whereRaw($normalizar('nombre').' LIKE ?', [$like])
                        ->orWhereRaw($normalizar("coalesce(dni, '')").' LIKE ?', [$like])
                        ->orWhereRaw($normalizar("coalesce(tutor, '')").' LIKE ?', [$like]);
                });
            }
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

        // 1.1 Filtro por periodo/mes: alumno inscrito en el periodo (tiene
        // un taller de ese periodo) o con algun horario programado en el.
        if ($request->filled('periodo_id')) {
            $periodoId = $request->periodo_id;
            $query->where(function ($q) use ($periodoId) {
                $q->whereHas('talleres', fn ($t) => $t->where('periodo_id', $periodoId))
                    ->orWhereHas('horarios', fn ($h) => $h->where('periodo_id', $periodoId));
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
        $nuevoEstado = array_key_exists('activo', $data) ? (bool) $data['activo'] : $alumno->activo;
        $cambioEstado = $nuevoEstado !== $alumno->activo;
        unset($data['activo']);

        $mensaje = 'Datos del alumno actualizados.';

        DB::transaction(function () use ($alumno, $data, $cambioEstado, $nuevoEstado, &$mensaje) {
            $alumno->update($data);

            if (! $cambioEstado) {
                return;
            }

            $talleres = $alumno->cambiarEstado($nuevoEstado);
            $mensaje = $nuevoEstado
                ? 'Alumno activado.'.($talleres ? " Se recuperaron {$talleres} taller(es) del periodo actual." : ' Agrégale un taller para inscribirlo en el periodo.')
                : 'Alumno inactivo.'.($talleres ? " Se dio de baja en {$talleres} taller(es) del periodo actual: sus clases pendientes se cancelaron y, si aún no tuvo clases, se quitó su mensualidad sin pagar." : '');
        });

        return response()->json(['ok' => true, 'message' => $mensaje, 'data' => $alumno->fresh()]);
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
            'veces_semana' => 'nullable|integer|between:1,7',
            'monto_mensual' => 'nullable|required_with:periodo_id|numeric|min:0|max:99999',
        ], [
            'monto_mensual.required_with' => 'Escribe la mensualidad que pagará el alumno por este taller.',
        ]);

        $taller = AlumnoTaller::create([
            'alumno_id' => $alumno->id,
            'especialidad_id' => $data['especialidad_id'],
            'maestro_id' => $data['maestro_id'] ?? null,
            'periodo_id' => $data['periodo_id'] ?? null,
            'estado' => 'activo',
        ] + AlumnoTaller::resolverMensualidad($data, $request->input('horarios')));

        if ($request->filled('horarios')) {
            HorarioService::generar($taller, $request->input('horarios'));
        }

        $taller->sincronizarPago();
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