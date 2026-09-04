<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use Illuminate\Http\Request;

class AvisoController extends Controller
{
    public function index()
    {
        $avisos = Aviso::with('autor')->latest()->paginate(15);

        return view('avisos.index', compact('avisos'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $data['creado_por'] = $request->user()->id;

        $aviso = Aviso::create($data);

        return response()->json(['ok' => true, 'message' => 'Aviso publicado correctamente.', 'data' => $aviso]);
    }

    public function edit(Aviso $aviso)
    {
        return response()->json(['ok' => true, 'data' => $aviso]);
    }

    public function update(Request $request, Aviso $aviso)
    {
        $data = $this->validarDatos($request);
        $aviso->update($data);

        return response()->json(['ok' => true, 'message' => 'Aviso actualizado correctamente.', 'data' => $aviso]);
    }

    public function destroy(Aviso $aviso)
    {
        $aviso->delete();

        return response()->json(['ok' => true, 'message' => 'Aviso eliminado.']);
    }

    /** El usuario cierra el popup flotante: lo desactivamos para que no vuelva a salir. */
    public function descartar(Aviso $aviso)
    {
        $aviso->update(['activo' => false]);

        return response()->json(['ok' => true]);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'titulo' => 'required|string|max:150',
            'mensaje' => 'required|string',
            'tipo' => 'required|in:info,advertencia,urgente',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'nullable|boolean',
        ], [
            'titulo.required' => 'El titulo del aviso es obligatorio.',
            'mensaje.required' => 'El mensaje del aviso es obligatorio.',
        ]);
    }
}
