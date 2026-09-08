<?php

namespace App\Services;

use App\Models\AlumnoTaller;
use App\Models\Clase;
use App\Models\Horario;
use App\Models\Periodo;
use Illuminate\Validation\ValidationException;

/**
 * Logica compartida para generar horarios semanales (y sus clases en el
 * calendario) a partir de un taller (AlumnoTaller). La usan tanto el
 * registro/edicion de alumnos (para su primer taller) como el modulo de
 * "Agregar otro taller", para evitar duplicar la misma logica dos veces.
 */
class HorarioService
{
    public static function generar(AlumnoTaller $taller, array $horariosInput): int
    {
        $validado = validator(['horarios' => $horariosInput], [
            'horarios' => 'required|array|min:1',
            'horarios.*.dia_semana' => 'required|integer|between:1,7',
            'horarios.*.hora_inicio' => 'required',
            'horarios.*.hora_fin' => 'required',
        ], [
            'horarios.required' => 'Selecciona al menos un dia de clase.',
            'horarios.*.hora_inicio.required' => 'Falta la hora de inicio en uno de los dias marcados.',
            'horarios.*.hora_fin.required' => 'Falta la hora de fin en uno de los dias marcados.',
        ])->validate();

        foreach ($validado['horarios'] as $i => $h) {
            if (strtotime($h['hora_fin']) <= strtotime($h['hora_inicio'])) {
                throw ValidationException::withMessages([
                    "horarios.$i.hora_fin" => 'La hora de fin debe ser posterior a la hora de inicio ('.Horario::DIAS[$h['dia_semana']].').',
                ]);
            }
        }

        if (! $taller->periodo_id) {
            throw ValidationException::withMessages([
                'periodo_id' => 'Selecciona un periodo para poder programar los horarios de este taller.',
            ]);
        }

        $periodo = Periodo::findOrFail($taller->periodo_id);
        $creadas = 0;

        $diasEnviados = collect($validado['horarios'])->pluck('dia_semana')->all();

        // Si se quito un dia de clase (se desmarco el checkbox), el horario
        // de ese dia para este taller+periodo se desactiva -- no se borra,
        // para no perder el historial de que ese dia SI se dicto en su
        // momento, pero deja de generar clases nuevas.
        Horario::where('alumno_taller_id', $taller->id)
            ->where('periodo_id', $periodo->id)
            ->whereNotIn('dia_semana', $diasEnviados)
            ->update(['activo' => false]);

        foreach ($validado['horarios'] as $h) {
            // La unicidad es por taller + PERIODO + dia. Esto es clave para
            // el historial: si el mismo taller se reutiliza para pasar a un
            // alumno a un nuevo periodo (cambiando su periodo_id o su
            // maestro), NO se pisa el horario del periodo anterior -- se
            // crea uno nuevo para el nuevo periodo, y el anterior queda
            // intacto para siempre poder consultar "con que maestro estuvo
            // en tal mes" sin importar si se edito el mismo taller o se
            // creo uno nuevo.
            $horario = Horario::updateOrCreate(
                ['alumno_taller_id' => $taller->id, 'periodo_id' => $periodo->id, 'dia_semana' => $h['dia_semana']],
                [
                    'alumno_id' => $taller->alumno_id,
                    'maestro_id' => $taller->maestro_id,
                    'especialidad_id' => $taller->especialidad_id,
                    'hora_inicio' => $h['hora_inicio'],
                    'hora_fin' => $h['hora_fin'],
                    'salon' => $taller->salon,
                    'activo' => true,
                ]
            );

            for ($fecha = $periodo->fecha_inicio->copy(); $fecha->lte($periodo->fecha_fin); $fecha->addDay()) {
                if ($fecha->isoWeekday() != $h['dia_semana']) {
                    continue;
                }

                $existe = Clase::where('horario_id', $horario->id)
                    ->whereDate('fecha', $fecha->toDateString())
                    ->exists();

                if ($existe) {
                    continue;
                }

                Clase::create([
                    'horario_id' => $horario->id,
                    'alumno_taller_id' => $taller->id,
                    'alumno_id' => $taller->alumno_id,
                    'maestro_id' => $horario->maestro_id,
                    'especialidad_id' => $horario->especialidad_id,
                    'periodo_id' => $periodo->id,
                    'fecha' => $fecha->toDateString(),
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'salon' => $horario->salon,
                    'estado' => 'programada',
                ]);

                $creadas++;
            }
        }

        return $creadas;
    }
}