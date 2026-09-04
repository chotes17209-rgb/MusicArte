<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Maestro;
use Illuminate\Http\Request;

class ClaseController extends Controller
{
    public function index()
    {
        $alumnos = Alumno::activos()->orderBy('nombre')->get();
        $maestros = Maestro::where('activo', true)->orderBy('nombre')->get();

        return view('calendario.index', compact('alumnos', 'maestros'));
    }

    /** Feed de eventos que consume FullCalendar via AJAX (?start=&end=). */
    public function eventos(Request $request)
    {
        $query = Clase::with(['alumno', 'maestro', 'especialidad', 'asistencia']);

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereDate('fecha', '>=', substr($request->start, 0, 10))
                  ->whereDate('fecha', '<=', substr($request->end, 0, 10));
        }
        if ($request->filled('maestro_id')) {
            $query->where('maestro_id', $request->maestro_id);
        }
        if ($request->filled('alumno_id')) {
            $query->where('alumno_id', $request->alumno_id);
        }

        $eventos = $query->get()->map->toCalendarEvent();

        return response()->json($eventos);
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $alumno = Alumno::find($data['alumno_id']);
        $data['especialidad_id'] = $data['especialidad_id'] ?? $alumno->especialidad_id;

        $clase = Clase::create($data);

        return response()->json(['ok' => true, 'message' => 'Clase programada correctamente.', 'data' => $clase]);
    }

    public function show(Clase $clase)
    {
        $clase->load(['alumno', 'maestro', 'especialidad', 'asistencia']);

        return response()->json(['ok' => true, 'data' => $clase]);
    }

    public function update(Request $request, Clase $clase)
    {
        $data = $this->validarDatos($request);
        $clase->update($data);

        return response()->json(['ok' => true, 'message' => 'Clase actualizada correctamente.', 'data' => $clase]);
    }

    /** Mover/redimensionar clase arrastrando en el calendario (drag & drop). */
    public function mover(Request $request, Clase $clase)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
        ]);

        $clase->update($data);

        return response()->json(['ok' => true, 'message' => 'Clase reprogramada.']);
    }

    public function cambiarEstado(Request $request, Clase $clase)
    {
        $data = $request->validate([
            'estado' => 'required|in:programada,realizada,cancelada',
        ]);

        $clase->update($data);

        return response()->json(['ok' => true, 'message' => 'Estado de la clase actualizado.', 'data' => $clase]);
    }

    public function destroy(Clase $clase)
    {
        $clase->delete();

        return response()->json(['ok' => true, 'message' => 'Clase eliminada del calendario.']);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'maestro_id' => 'nullable|exists:maestros,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'salon' => 'nullable|string|max:50',
            'estado' => 'nullable|in:programada,realizada,cancelada',
            'notas' => 'nullable|string',
        ], [
            'alumno_id.required' => 'Selecciona un alumno.',
            'fecha.required' => 'Selecciona la fecha de la clase.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);
    }
}
