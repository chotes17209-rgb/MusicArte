<?php

namespace App\Http\Controllers;

use App\Models\Recital;
use Illuminate\Http\Request;

class RecitalController extends Controller
{
    public function index()
    {
        $recitales = Recital::orderByDesc('fecha')->get();

        return view('recitales.index', compact('recitales'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $recital = Recital::create($data);

        return response()->json(['ok' => true, 'message' => 'Recital/evento creado correctamente.', 'data' => $recital]);
    }

    public function edit(Recital $recital)
    {
        return response()->json(['ok' => true, 'data' => $recital]);
    }

    public function update(Request $request, Recital $recital)
    {
        $data = $this->validarDatos($request);
        $recital->update($data);

        return response()->json(['ok' => true, 'message' => 'Recital/evento actualizado.', 'data' => $recital]);
    }

    public function destroy(Recital $recital)
    {
        $recital->delete();

        return response()->json(['ok' => true, 'message' => 'Recital/evento eliminado.']);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:150',
            'fecha' => 'nullable|date',
            'tema' => 'nullable|string|max:150',
            'descripcion' => 'nullable|string',
            'participantes' => 'nullable|string',
            'pago_por_alumno' => 'nullable|numeric|min:0',
        ], [
            'nombre.required' => 'El nombre del recital/evento es obligatorio.',
        ]);
    }
}
