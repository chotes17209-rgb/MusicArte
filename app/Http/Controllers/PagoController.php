<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Especialidad;
use App\Models\Maestro;
use App\Models\Pago;
use App\Models\PagoAbono;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $periodoActual = Periodo::orderByDesc('anio')->orderByDesc('mes')->first();
        $periodoId = $request->filled('periodo_id') ? (int) $request->periodo_id : ($periodoActual->id ?? null);

        $query = Pago::with(['alumno', 'especialidad', 'maestro', 'periodo', 'abonos']);

        if ($periodoId) {
            $query->where('periodo_id', $periodoId);
        }
        if ($request->filled('maestro_id')) {
            $query->delMaestro((int) $request->maestro_id);
        }
        if ($request->filled('especialidad_id')) {
            $query->delTaller((int) $request->especialidad_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('buscar')) {
            $texto = $request->buscar;
            $query->whereHas('alumno', fn ($q) => $q->where('nombre', 'like', "%{$texto}%"));
        }

        $pagos = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $alumnos = Alumno::activos()->orderBy('nombre')->get();
        $especialidades = Especialidad::where('activo', true)->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('anio')->orderByDesc('mes')->get();

        $totales = [
            'facturado' => (clone $query)->sum('monto_total'),
            'recaudado' => (clone $query)->sum('monto_pagado'),
            'pendiente' => (clone $query)->sum('saldo'),
        ];

        return view('pagos.index', compact('pagos', 'alumnos', 'especialidades', 'maestros', 'periodos', 'periodoId', 'totales'));
    }

    /** Req. 13: historial de pagos de todo un año, mes a mes. */
    public function anual(Request $request)
    {
        $anio = (int) $request->get('anio', now()->year);

        $resumen = collect(range(1, 12))->map(function ($mes) use ($anio) {
            $periodo = Periodo::where('mes', $mes)->where('anio', $anio)->first();
            $q = Pago::query();
            if ($periodo) {
                $q->where('periodo_id', $periodo->id);
            } else {
                $q->where('mes', $mes)->where('anio', $anio);
            }

            return [
                'mes' => Pago::MESES[$mes],
                'facturado' => (clone $q)->sum('monto_total'),
                'recaudado' => (clone $q)->sum('monto_pagado'),
                'pendiente' => (clone $q)->sum('saldo'),
                'cantidad' => (clone $q)->count(),
            ];
        });

        return view('pagos.anual', compact('resumen', 'anio'));
    }

    // El middleware 'role:admin' protege store/update/destroy/abonos a nivel de ruta (req. 15).

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $data['usuario_id'] = auth()->id();
        $data['monto_pagado'] = 0;
        $data['saldo'] = $data['monto_total'];
        $data['estado'] = 'pendiente';

        if ($periodo = Periodo::find($data['periodo_id'] ?? null)) {
            $data['mes'] = $periodo->mes;
            $data['anio'] = $periodo->anio;
        }

        $pago = Pago::create($data);

        // Req. 10: se puede dejar registrado el primer abono al crear la deuda.
        if ($request->filled('primer_abono.monto')) {
            $this->crearAbono($pago, $request->input('primer_abono'));
        }

        return response()->json(['ok' => true, 'message' => 'Pago registrado correctamente.', 'data' => $pago->fresh('abonos')]);
    }

    public function edit(Pago $pago)
    {
        $pago->load(['abonos.usuario', 'alumno', 'especialidad', 'maestro', 'periodo']);

        return response()->json(['ok' => true, 'data' => $pago]);
    }

    public function update(Request $request, Pago $pago)
    {
        $data = $this->validarDatos($request);

        if ($periodo = Periodo::find($data['periodo_id'] ?? null)) {
            $data['mes'] = $periodo->mes;
            $data['anio'] = $periodo->anio;
        }

        $pago->update($data);
        $pago->recalcular(); // el monto_total pudo cambiar: recalcula saldo/estado

        return response()->json(['ok' => true, 'message' => 'Pago actualizado correctamente.', 'data' => $pago->fresh('abonos')]);
    }

    public function destroy(Pago $pago)
    {
        $pago->delete(); // cascade elimina tambien sus abonos

        return response()->json(['ok' => true, 'message' => 'Registro de pago eliminado.']);
    }

    /**
     * Req. 10: registrar un nuevo abono (pago a cuenta) sobre una deuda ya
     * existente. El saldo se recalcula solo (evento en PagoAbono::booted()).
     */
    public function storeAbono(Request $request, Pago $pago)
    {
        $abono = $this->crearAbono($pago, $request->all());

        return response()->json([
            'ok' => true,
            'message' => 'Abono registrado. Saldo actualizado.',
            'data' => $pago->fresh('abonos'),
            'abono' => $abono,
        ]);
    }

    public function destroyAbono(Pago $pago, PagoAbono $abono)
    {
        abort_if($abono->pago_id !== $pago->id, 404);

        $abono->delete();

        return response()->json(['ok' => true, 'message' => 'Abono eliminado. Saldo actualizado.', 'data' => $pago->fresh('abonos')]);
    }

    private function crearAbono(Pago $pago, array $input): PagoAbono
    {
        $data = validator($input, [
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|in:'.implode(',', array_keys(PagoAbono::METODOS)),
            'recibo_nro' => 'nullable|string|max:50',
        ], [
            'monto.required' => 'El monto del abono es obligatorio.',
            'metodo_pago.required' => 'Selecciona el metodo de pago.',
        ])->validate();

        $data['pago_id'] = $pago->id;
        $data['usuario_id'] = auth()->id();

        return DB::transaction(fn () => PagoAbono::create($data));
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'periodo_id' => 'required|exists:periodos,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'concepto' => 'nullable|string|max:150',
            'monto_total' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
        ], [
            'alumno_id.required' => 'Selecciona un alumno.',
            'periodo_id.required' => 'Selecciona el periodo de este pago.',
            'monto_total.required' => 'El monto total es obligatorio.',
        ]);
    }
}
