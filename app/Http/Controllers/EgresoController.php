<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use Illuminate\Http\Request;

class EgresoController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $egresos = Egreso::whereMonth('fecha', $mes)->whereYear('fecha', $anio)
            ->orderByDesc('fecha')->paginate(20)->withQueryString();

        $totalMes = Egreso::whereMonth('fecha', $mes)->whereYear('fecha', $anio)->sum('total');

        return view('egresos.index', compact('egresos', 'mes', 'anio', 'totalMes'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $data['total'] = $data['yape_bcp'] + $data['plin_ibk'] + $data['tarjeta'] + $data['efectivo'];

        $egreso = Egreso::create($data);

        return response()->json(['ok' => true, 'message' => 'Egreso registrado correctamente.', 'data' => $egreso]);
    }

    public function edit(Egreso $egreso)
    {
        return response()->json(['ok' => true, 'data' => $egreso]);
    }

    public function update(Request $request, Egreso $egreso)
    {
        $data = $this->validarDatos($request);
        $data['total'] = $data['yape_bcp'] + $data['plin_ibk'] + $data['tarjeta'] + $data['efectivo'];

        $egreso->update($data);

        return response()->json(['ok' => true, 'message' => 'Egreso actualizado correctamente.', 'data' => $egreso]);
    }

    public function destroy(Egreso $egreso)
    {
        $egreso->delete();

        return response()->json(['ok' => true, 'message' => 'Egreso eliminado.']);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'fecha' => 'required|date',
            'detalle' => 'required|string|max:255',
            'yape_bcp' => 'nullable|numeric|min:0',
            'plin_ibk' => 'nullable|numeric|min:0',
            'tarjeta' => 'nullable|numeric|min:0',
            'efectivo' => 'nullable|numeric|min:0',
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'detalle.required' => 'El detalle del egreso es obligatorio.',
        ]);
    }
}
