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
