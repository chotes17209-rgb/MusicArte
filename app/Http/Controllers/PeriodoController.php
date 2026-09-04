<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::orderByDesc('anio')->orderByDesc('mes')->get();

        return view('periodos.index', compact('periodos'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $periodo = Periodo::create($data);

        return response()->json(['ok' => true, 'message' => "Periodo '{$periodo->nombre}' creado correctamente.", 'data' => $periodo]);
    }

    public function edit(Periodo $periodo)
    {
        return response()->json(['ok' => true, 'data' => $periodo]);
    }

    public function update(Request $request, Periodo $periodo)
    {
        $data = $this->validarDatos($request, $periodo->id);
        $periodo->update($data);

        return response()->json(['ok' => true, 'message' => 'Periodo actualizado correctamente.', 'data' => $periodo]);
    }

    public function destroy(Periodo $periodo)
    {
        if ($periodo->horarios()->exists() || $periodo->clases()->exists()) {
            return response()->json(['ok' => false, 'message' => 'No se puede eliminar: hay horarios o clases usando este periodo.'], 422);
        }

        $periodo->delete();

        return response()->json(['ok' => true, 'message' => 'Periodo eliminado.']);
    }

    private function validarDatos(Request $request, $ignoreId = null): array
    {
        $data = $request->validate([
            'mes' => [
                'required', 'integer', 'between:1,12',
                Rule::unique('periodos')->where(fn ($q) => $q->where('anio', $request->anio))->ignore($ignoreId),
            ],
            'anio' => 'required|integer|min:2020|max:2100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'nullable|boolean',
        ], [
            'mes.required' => 'Selecciona el mes.',
            'mes.unique' => 'Ya existe un periodo para ese mes y ano.',
            'anio.required' => 'Indica el ano.',
        ]);

        // Si no se indican fechas exactas, se calculan automaticamente 4 semanas desde el dia 1 del mes.
        if (empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
            [$data['fecha_inicio'], $data['fecha_fin']] = Periodo::sugerirRango($data['mes'], $data['anio']);
        }

        $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                  7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $data['nombre'] = $meses[$data['mes']].' '.$data['anio'];

        return $data;
    }
}