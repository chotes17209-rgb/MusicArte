<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::withCount(['alumnos', 'maestros'])->orderBy('nombre')->get();

        return view('especialidades.index', compact('especialidades'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);

        // Solo el admin puede fijar el precio; recepcion siempre guarda 0 aunque lo envie manipulado.
        if (! $request->user()->esAdmin()) {
            $data['precio_mensual'] = 0;
        }

        $especialidad = Especialidad::create($data);

        return response()->json(['ok' => true, 'message' => "Especialidad '{$especialidad->nombre}' creada correctamente.", 'data' => $especialidad]);
    }

    public function edit(Especialidad $especialidad)
    {
        return response()->json(['ok' => true, 'data' => $especialidad]);
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $data = $this->validarDatos($request, $especialidad->id);

        if (! $request->user()->esAdmin()) {
            unset($data['precio_mensual']);
        }

        $especialidad->update($data);

        return response()->json(['ok' => true, 'message' => 'Especialidad actualizada correctamente.', 'data' => $especialidad]);
    }

    public function destroy(Request $request, Especialidad $especialidad)
    {
        if (! $request->user()->esAdmin()) {
            return response()->json(['ok' => false, 'message' => 'Solo el administrador puede eliminar especialidades.'], 403);
        }

        if ($especialidad->alumnos()->exists()) {
            return response()->json(['ok' => false, 'message' => 'No se puede eliminar: hay alumnos asignados a esta especialidad.'], 422);
        }

        $especialidad->delete();

        return response()->json(['ok' => true, 'message' => 'Especialidad eliminada.']);
    }

    private function validarDatos(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:100|unique:especialidades,nombre,'.$ignoreId,
            'color' => 'required|string|max:7',
            'precio_mensual' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ], [
            'nombre.required' => 'El nombre de la especialidad es obligatorio.',
            'nombre.unique' => 'Ya existe una especialidad con ese nombre.',
        ]);
    }
}
