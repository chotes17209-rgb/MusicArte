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

        // Año de la matricula al registrar un alumno: el del periodo con que se trabaja.
        $anioMatricula = Periodo::seleccionado()->anio ?? now()->year;

        return view('alumnos.index', compact('alumnos', 'estados', 'especialidades', 'maestros', 'periodos', 'todosPeriodos', 'anioMatricula'));
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

        // Al pasar a un periodo nuevo, los anteriores quedan inactivos en su
        // historial: se anota a que periodo paso (el activo mas cercano posterior).
        $posterior = null;
        $lineaDeTiempo = $lineaDeTiempo->map(function ($item) use (&$posterior) {
            $item['paso_a'] = $posterior;
            if ($item['estado'] === 'activo') {
                $posterior = $item['periodo']->nombre;
            }

            return $item;
        });

        $pagos = $alumno->pagos()->orderByDesc('anio')->orderByDesc('mes')->limit(12)->get();
        // Solo los talleres del periodo vigente (los de meses anteriores son historial).
        $tallerActual = $alumno->talleresVigentes()->where('estado', 'activo')->with(['especialidad', 'maestro', 'periodo', 'horarios' => fn ($q) => $q->where('activo', true)])->get();

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

        $inactivosGeneral = $alumnos->where('activo', false)->pluck('id')->flip();

        // Quien ya paso a un periodo posterior queda inactivo en este, aunque el mes no haya terminado.
        $siguiente = $periodo->siguientePeriodoDe($ids);
        $finalizado = $periodo->finalizado();

        return $ids->mapWithKeys(fn ($id) => [$id => match (true) {
            isset($inactivosEnPeriodo[$id]) => ['texto' => $finalizado ? 'No estudió este mes' : 'Inactivo este mes', 'clase' => 'bg-secondary'],
            $siguiente->has($id) => ['texto' => 'Inactivo · pasó a '.$siguiente[$id], 'clase' => 'bg-secondary'],
            $finalizado => ['texto' => 'Inactivo · no continuó', 'clase' => 'bg-warning'],
            isset($inactivosGeneral[$id]) => ['texto' => 'Inactivo este mes', 'clase' => 'bg-secondary'],
            default => ['texto' => 'Activo', 'clase' => 'bg-success'],
        }])->all();
    }

    private function aplicarFiltros(Request $request)
    {
        $query = Alumno::with(['especialidad', 'maestro'])
            // Con un periodo elegido, solo cuenta los talleres de ese periodo.
            ->withCount(['talleres as talleres_activos_count' => fn ($q) => $q->where('estado', 'activo')
                ->when($request->filled('periodo_id'), fn ($t) => $t->where('periodo_id', $request->periodo_id))]);

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

        // Taller y maestro se buscan en los talleres del periodo elegido (o de
        // los periodos vigentes): un alumno que cambio de maestro ya no
        // aparece en la lista de su maestro anterior.
        // (En un mes pasado se consideran todos sus talleres: al importar el
        // Excel quedaron como historial; en los vigentes, solo los activos.)
        $delPeriodo = fn ($qt) => $request->filled('periodo_id')
            ? $qt->where('periodo_id', $request->periodo_id)
                ->when(! Periodo::find($request->periodo_id)?->finalizado(), fn ($q) => $q->where('estado', 'activo'))
            : $qt->where('estado', 'activo')->whereHas('periodo', fn ($p) => $p->vigentes());

        if ($request->filled('especialidad_id')) {
            $especialidadId = $request->especialidad_id;
            $query->whereHas('talleres', fn ($qt) => $delPeriodo($qt->where('especialidad_id', $especialidadId)));
        }

        if ($request->filled('maestro_id')) {
            $maestroId = $request->maestro_id;
            $query->whereHas('talleres', fn ($qt) => $delPeriodo($qt->where('maestro_id', $maestroId)));
        }

        // Talleres que se muestran en la columna "Taller": los de ese periodo.
        $query->with(['talleres' => fn ($qt) => $delPeriodo($qt)->with(['especialidad', 'maestro'])->orderBy('id')]);

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        // 1.1 Filtro por periodo/mes: los alumnos que estudian en ese periodo
        // (taller u horario activo, o marcados activos al pasarlos). Los dados
        // de baja no aparecen, salvo que se filtre por "Inactivos".
        if ($request->filled('periodo_id')) {
            $periodoId = $request->periodo_id;

            if ($request->estado === 'inactivo') {
                $query->where(fn ($q) => $q->whereHas('talleres', fn ($t) => $t->where('periodo_id', $periodoId))
                    ->orWhereHas('historialPeriodos', fn ($h) => $h->where('periodo_id', $periodoId)));
            } else {
                $query->where(fn ($q) => $q->whereHas('talleres', fn ($t) => $t->where('periodo_id', $periodoId)->where('estado', 'activo'))
                    ->orWhereHas('horarios', fn ($h) => $h->where('periodo_id', $periodoId)->where('activo', true))
                    ->orWhereHas('historialPeriodos', fn ($h) => $h->where('periodo_id', $periodoId)->where('estado', 'activo')))
                    ->whereDoesntHave('historialPeriodos', fn ($h) => $h->where('periodo_id', $periodoId)->where('estado', 'inactivo'));
            }
        }

        return $query;
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);

        $matricula = $request->validate([
            'matricula_monto' => 'nullable|numeric|min:0|max:99999',
            'matricula_nota' => 'nullable|string|max:255',
            'matricula_anio' => 'nullable|integer|min:2020|max:2100',
        ]);

        $alumno = DB::transaction(function () use ($request, $data, $matricula) {
            $alumno = Alumno::create($data);
            $this->crearTallerInicial($request, $alumno);

            // Matricula del año (se paga una vez por año), aparte de la mensualidad.
            if (($matricula['matricula_monto'] ?? null) !== null) {
                $periodo = $request->filled('periodo_id') ? Periodo::find($request->periodo_id) : null;
                $alumno->registrarMatricula(
                    (int) ($matricula['matricula_anio'] ?? $periodo->anio ?? now()->year),
                    $matricula['matricula_monto'],
                    $matricula['matricula_nota'] ?? null,
                    $periodo->mes ?? null,
                );
            }

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

        $anio = Periodo::seleccionado()->anio ?? now()->year;
        $data = $alumno->toArray();
        $data['matricula_anio'] = $anio;
        $data['matricula'] = $alumno->matriculaDe($anio)?->only(['id', 'monto_total', 'saldo', 'estado', 'observacion']);

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /**
     * Registra o corrige la matricula del año de un alumno ya creado. Es un
     * pago aparte (una sola vez por año); sus abonos se registran en Pagos.
     */
    public function matricula(Request $request, Alumno $alumno)
    {
        $data = $request->validate([
            'anio' => 'required|integer|min:2020|max:2100',
            'monto' => 'required|numeric|min:0|max:99999',
            'nota' => 'nullable|string|max:255',
        ], [
            'monto.required' => 'Escribe el monto de la matrícula.',
        ]);

        $pago = $alumno->registrarMatricula((int) $data['anio'], $data['monto'], $data['nota'] ?? null);

        return response()->json([
            'ok' => true,
            'message' => "Matrícula {$data['anio']} registrada: S/ ".number_format($pago->monto_total, 2).'.',
            'data' => $pago->only(['id', 'monto_total', 'saldo', 'estado', 'observacion']),
        ]);
    }

    public function update(Request $request, Alumno $alumno)
    {
        $data = $this->validarDatos($request);
        $nuevoEstado = array_key_exists('activo', $data) ? (bool) $data['activo'] : $alumno->activo;
        // Inactivo con talleres aun activos en el periodo vigente tambien se
        // aplica (pudo quedar desactivado antes sin darse de baja del periodo).
        $cambioEstado = $nuevoEstado !== $alumno->activo
            || (! $nuevoEstado && $alumno->talleresVigentes()->where('estado', 'activo')->exists());
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
            'nota_mensualidad' => 'nullable|string|max:255',
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