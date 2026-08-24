<?php

namespace App\Http\Controllers;

use App\Models\CajaChica;
use Illuminate\Http\Request;

class CajaChicaController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $movimientos = CajaChica::whereMonth('fecha', $mes)->whereYear('fecha', $anio)
            ->orderByDesc('fecha')->paginate(20)->withQueryString();

        $totalMes = CajaChica::whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('monto');

        return view('caja-chica.index', compact('movimientos', 'mes', 'anio', 'totalMes'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $movimiento = CajaChica::create($data);

        return response()->json(['ok' => true, 'message' => 'Movimiento de caja chica registrado.', 'data' => $movimiento]);
    }

    public function edit(CajaChica $caja_chica)
    {
        return response()->json(['ok' => true, 'data' => $caja_chica]);
    }

    public function update(Request $request, CajaChica $caja_chica)
    {
        $data = $this->validarDatos($request);
        $caja_chica->update($data);

        return response()->json(['ok' => true, 'message' => 'Movimiento actualizado.', 'data' => $caja_chica]);
    }

    public function destroy(CajaChica $caja_chica)
    {
        $caja_chica->delete();

        return response()->json(['ok' => true, 'message' => 'Movimiento eliminado.']);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'fecha' => 'required|date',
            'proveedor' => 'nullable|string|max:150',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'descripcion.required' => 'La descripcion es obligatoria.',
        ]);
    }
}
