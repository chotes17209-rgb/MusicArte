<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Especialidad;
use App\Models\Maestro;
use App\Models\Pago;
use App\Models\PagoAbono;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Seccion 9, 12 y 14: listado de pagos (deudas/conceptos) del periodo,
     * con filtros por alumno, taller (especialidad), maestro y estado.
     */
    public function index(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        $query = Pago::with(['alumno', 'alumnoTaller.especialidad', 'alumnoTaller.maestro', 'abonos'])
            ->where('mes', $mes)->where('anio', $anio);

        if ($request->filled('buscar')) {
            $query->whereHas('alumno', fn ($q) => $q->where('nombre', 'like', '%'.$request->buscar.'%'));
        }
        if ($request->boolean('solo_pendientes')) {
            $query->where('saldo', '>', 0);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        // Filtro por taller (seccion 12/14): el "taller" del pago es la
        // especialidad del taller especifico que se le asigno al crearlo.
        if ($request->filled('especialidad_id')) {
            $especialidadId = $request->especialidad_id;
            $query->whereHas('alumnoTaller', fn ($q) => $q->where('especialidad_id', $especialidadId));
        }
        if ($request->filled('maestro_id')) {
            $maestroId = $request->maestro_id;
            $query->whereHas('alumnoTaller', fn ($q) => $q->where('maestro_id', $maestroId));
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate(20)->withQueryString();

        $alumnos = Alumno::activos()->orderBy('nombre')->get();
        $especialidades = Especialidad::orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        $totalesQuery = (clone $query);
        $totales = [
            'recaudado' => (clone $totalesQuery)->get()->sum(fn ($p) => $p->monto_total - $p->saldo),
            'pendiente' => (clone $totalesQuery)->sum('saldo'),
        ];

        return view('pagos.index', compact('pagos', 'alumnos', 'especialidades', 'maestros', 'mes', 'anio', 'totales'));
    }

    /** Talleres activos de un alumno especifico, para el selector del modal (AJAX). */
    public function talleresDeAlumno(Alumno $alumno)
    {
        $talleres = $alumno->talleres()->with('especialidad', 'maestro')->get();

        return response()->json(['ok' => true, 'data' => $talleres]);
    }

    // El middleware 'role:admin' protege store/update/destroy a nivel de ruta.

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $data['saldo'] = $data['monto_total'];
        $data['estado'] = $data['monto_total'] <= 0 ? 'pagado' : 'pendiente';

        $pago = Pago::create($data);

        return response()->json(['ok' => true, 'message' => 'Pago registrado correctamente.', 'data' => $pago]);
    }

    public function edit(Pago $pago)
    {
        return response()->json(['ok' => true, 'data' => $pago->load('abonos', 'alumno', 'alumnoTaller.especialidad', 'alumnoTaller.maestro')]);
    }

    public function update(Request $request, Pago $pago)
    {
        $data = $this->validarDatos($request);

        $pago->update($data);
        $pago->recalcular();

        return response()->json(['ok' => true, 'message' => 'Pago actualizado correctamente.', 'data' => $pago]);
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();

        return response()->json(['ok' => true, 'message' => 'Registro de pago eliminado.']);
    }

    /**
     * Seccion 10: registrar un abono (pago a cuenta) sobre un pago/deuda
     * existente. Cada abono queda guardado de forma individual, con su
     * propio recibo, fecha y metodo de pago, y el saldo se recalcula
     * automaticamente a partir de la suma de todos los abonos.
     */
    public function registrarAbono(Request $request, Pago $pago)
    {
        $data = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|in:transferencia,yape,efectivo',
            'recibo_nro' => 'nullable|string|max:50',
            'observacion' => 'nullable|string',
        ], [
            'monto.required' => 'Indica el monto del abono.',
            'monto.min' => 'El monto del abono debe ser mayor a cero.',
        ]);

        if ($pago->saldo <= 0) {
            return response()->json(['ok' => false, 'message' => 'Este pago ya esta saldado por completo.'], 422);
        }

        if ($data['monto'] > $pago->saldo + 0.009) {
            return response()->json([
                'ok' => false,
                'message' => 'El abono (S/ '.number_format($data['monto'], 2).') no puede superar el saldo pendiente (S/ '.number_format($pago->saldo, 2).').',
            ], 422);
        }

        $data['pago_id'] = $pago->id;
        $data['registrado_por'] = $request->user()->id;

        $abono = PagoAbono::create($data);
        $pago->recalcular();

        return response()->json([
            'ok' => true,
            'message' => 'Abono registrado correctamente.',
            'data' => ['abono' => $abono, 'pago' => $pago->fresh()],
        ]);
    }

    /** Corrige un abono mal registrado (solo admin). Recalcula el pago despues de eliminarlo. */
    public function eliminarAbono(PagoAbono $abono)
    {
        $pago = $abono->pago;
        $abono->delete();
        $pago->recalcular();

        return response()->json(['ok' => true, 'message' => 'Abono eliminado y saldo recalculado.', 'data' => $pago->fresh()]);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'alumno_taller_id' => 'nullable|exists:alumno_talleres,id',
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|min:2020|max:2100',
            'concepto' => 'nullable|string|max:150',
            'monto_total' => 'required|numeric|min:0',
            'fecha_pago' => 'nullable|date',
            'observacion' => 'nullable|string',
        ], [
            'alumno_id.required' => 'Selecciona un alumno.',
            'monto_total.required' => 'El monto total es obligatorio.',
        ]);
    }
}
