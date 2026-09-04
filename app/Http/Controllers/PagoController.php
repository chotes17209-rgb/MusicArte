<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $query = Pago::with('alumno')->where('mes', $mes)->where('anio', $anio);

        if ($request->filled('buscar')) {
            $query->whereHas('alumno', fn ($q) => $q->where('nombre', 'like', '%'.$request->buscar.'%'));
        }
        if ($request->boolean('solo_pendientes')) {
            $query->where('saldo', '>', 0);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate(20)->withQueryString();
        $alumnos = Alumno::activos()->orderBy('nombre')->get();

        $totales = [
            'recaudado' => (clone $query)->sum('monto_total'),
            'pendiente' => (clone $query)->sum('saldo'),
        ];

        return view('pagos.index', compact('pagos', 'alumnos', 'mes', 'anio', 'totales'));
    }

    // El middleware 'role:admin' protege store/update/destroy a nivel de ruta.

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $data['saldo'] = max(0, $data['monto_total'] - ($data['yape_transferencia'] + $data['efectivo'] + $data['tarjeta']));

        $pago = Pago::create($data);

        return response()->json(['ok' => true, 'message' => 'Pago registrado correctamente.', 'data' => $pago]);
    }

    public function edit(Pago $pago)
    {
        return response()->json(['ok' => true, 'data' => $pago]);
    }

    public function update(Request $request, Pago $pago)
    {
        $data = $this->validarDatos($request);
        $data['saldo'] = max(0, $data['monto_total'] - ($data['yape_transferencia'] + $data['efectivo'] + $data['tarjeta']));

        $pago->update($data);

        return response()->json(['ok' => true, 'message' => 'Pago actualizado correctamente.', 'data' => $pago]);
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();

        return response()->json(['ok' => true, 'message' => 'Registro de pago eliminado.']);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|min:2020|max:2100',
            'concepto' => 'nullable|string|max:150',
            'monto_total' => 'required|numeric|min:0',
            'yape_transferencia' => 'nullable|numeric|min:0',
            'efectivo' => 'nullable|numeric|min:0',
            'tarjeta' => 'nullable|numeric|min:0',
            'recibo_nro' => 'nullable|string|max:50',
            'fecha_pago' => 'nullable|date',
            'observacion' => 'nullable|string',
        ], [
            'alumno_id.required' => 'Selecciona un alumno.',
            'monto_total.required' => 'El monto total es obligatorio.',
        ]);
    }
}
