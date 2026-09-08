<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoPeriodo;
use App\Models\Periodo;

class HistorialController extends Controller
{
    /**
     * Seccion 7: matriz de alumnos x periodos mostrando en que meses
     * estuvo activo o inactivo cada alumno. La busqueda por nombre se
     * hace en el navegador (seccion 1.3, reactiva) ya que la tabla completa
     * se carga una sola vez.
     */
    public function index()
    {
        $periodos = Periodo::orderBy('anio')->orderBy('mes')->get();
        $alumnos = Alumno::orderBy('nombre')->get(['id', 'nombre']);

        $registros = AlumnoPeriodo::all()
            ->groupBy('alumno_id')
            ->map(fn ($grupo) => $grupo->keyBy('periodo_id'));

        return view('alumnos.historial', compact('periodos', 'alumnos', 'registros'));
    }
}