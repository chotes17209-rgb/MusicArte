<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Maestro;
use Illuminate\Http\Request;

class MaestroController extends Controller
{
    public function index()
    {
        $maestros = Maestro::with('especialidades')->withCount('alumnos')->orderBy('nombre')->get();
        $especialidades = Especialidad::where('activo', true)->orderBy('nombre')->get();

        return view('maestros.index', compact('maestros', 'especialidades'));
    }

    /**
     * "Ver" maestro (Read del CRUD). Muestra su tablero de horarios estilo
     * el cuadro fisico que se usaba en salon: una fila por hora, una
     * columna por dia, con el alumno (y su edad) en cada casilla — para un
     * periodo especifico, ya que el maestro y los horarios pueden cambiar
     * de un mes a otro.
     */
    public function show(Maestro $maestro, Request $request)
    {
        $maestro->load('especialidades');

        $periodoId = $request->get('periodo_id');
        $periodo = $periodoId
            ? \App\Models\Periodo::find($periodoId)
            : \App\Models\Periodo::where('activo', true)->orderByDesc('anio')->orderByDesc('mes')->first();

        $horarios = collect();
        if ($periodo) {
            $horarios = \App\Models\Horario::with(['alumno', 'especialidad'])
                ->where('maestro_id', $maestro->id)
                ->where('periodo_id', $periodo->id)
                ->orderBy('dia_semana')->orderBy('hora_inicio')
                ->get();
        }

        $periodos = \App\Models\Periodo::orderByDesc('anio')->orderByDesc('mes')->get();

        return view('maestros.show', compact('maestro', 'horarios', 'periodo', 'periodos'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $especialidades = $data['especialidades'] ?? [];
        unset($data['especialidades']);

        $maestro = Maestro::create($data);
        $maestro->especialidades()->sync($this->pivotDesdeEspecialidades($especialidades));

        return response()->json(['ok' => true, 'message' => "Maestro '{$maestro->nombre}' registrado correctamente.", 'data' => $maestro->load('especialidades')]);
    }

    public function edit(Maestro $maestro)
    {
        $maestro->load('especialidades:id,nombre');

        return response()->json(['ok' => true, 'data' => $maestro]);
    }

    public function update(Request $request, Maestro $maestro)
    {
        $data = $this->validarDatos($request);
        $especialidades = $data['especialidades'] ?? [];
        unset($data['especialidades']);

        $maestro->update($data);
        $maestro->especialidades()->sync($this->pivotDesdeEspecialidades($especialidades));

        return response()->json(['ok' => true, 'message' => 'Datos del maestro actualizados.', 'data' => $maestro->load('especialidades')]);
    }

    public function destroy(Maestro $maestro)
    {
        if ($maestro->alumnos()->exists()) {
            return response()->json(['ok' => false, 'message' => 'No se puede eliminar: el maestro tiene alumnos asignados.'], 422);
        }

        $maestro->delete();

        return response()->json(['ok' => true, 'message' => 'Maestro eliminado.']);
    }

    /** Convierte [{id, tarifa}, ...] al formato que espera sync() con datos de pivote. */
    private function pivotDesdeEspecialidades(array $especialidades): array
    {
        $pivotData = [];
        foreach ($especialidades as $esp) {
            $pivotData[$esp['id']] = ['tarifa_hora' => $esp['tarifa'] ?? 0];
        }

        return $pivotData;
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:150',
            'especialidades' => 'nullable|array',
            'especialidades.*.id' => 'required_with:especialidades|exists:especialidades,id',
            'especialidades.*.tarifa' => 'nullable|numeric|min:0',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'activo' => 'nullable|boolean',
        ], [
            'nombre.required' => 'El nombre del maestro es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
        ]);
    }
}
