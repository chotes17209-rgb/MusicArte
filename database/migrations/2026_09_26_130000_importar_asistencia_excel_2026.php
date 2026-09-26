<?php

use App\Models\Alumno;
use App\Models\AlumnoTaller;
use App\Models\Clase;
use App\Models\Periodo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Carga la hoja ASISTENCIA de ASISTENCIA_Y_HORARIOS_2026.xlsx (Enero a
 * Setiembre): en el Excel cada clase es una celda con la fecha, pintada de
 * verde si el alumno asistio y de amarillo/rojo si falto (con un
 * comentario cuando se reprogramo). Ya viene interpretada en
 * database/data/asistencia_2026.json (ver extraer_asistencia.py).
 *
 * Por cada celda marcada: se busca la clase de ese taller en esa fecha (si
 * el calendario no la tenia, se crea con la hora del horario del taller) y
 * se registra la asistencia. Nunca pisa una asistencia que ya se haya
 * marcado a mano en la app. Corre una sola vez (es una migracion).
 */
return new class extends Migration
{
    public function up(): void
    {
        $registros = json_decode(file_get_contents(database_path('data/asistencia_2026.json')), true);

        DB::transaction(function () use ($registros) {
            $periodos = Periodo::where('anio', 2026)->get()->keyBy('mes');
            $alumnos = Alumno::all()->keyBy(fn ($a) => $this->clave($a->nombre));
            $especialidades = DB::table('especialidades')->pluck('id', 'nombre');
            $maestros = DB::table('maestros')->pluck('id', 'nombre');

            // Talleres de 2026 con sus horarios y clases, precargados para no
            // hacer una consulta por cada una de las ~5.500 celdas.
            $talleres = AlumnoTaller::with('horarios')->whereIn('periodo_id', $periodos->pluck('id'))->get()
                ->keyBy(fn ($t) => $t->alumno_id.'|'.$t->especialidad_id.'|'.$t->maestro_id.'|'.$t->periodo_id);
            $clases = Clase::whereIn('alumno_taller_id', $talleres->pluck('id'))->get()
                ->keyBy(fn ($c) => $c->alumno_taller_id.'|'.$c->fecha->toDateString());
            $yaMarcadas = DB::table('asistencias')->pluck('clase_id')->flip();

            $nuevas = [];
            $realizadas = [];
            $ahora = now();

            foreach ($registros as $r) {
                $alumno = $alumnos->get($r['alumno']);
                $periodo = $periodos->get($r['mes']);
                if (! $alumno || ! $periodo) {
                    continue;
                }
                $taller = $talleres->get($alumno->id.'|'.($especialidades[$r['especialidad']] ?? '').'|'.($maestros[$r['maestro']] ?? '').'|'.$periodo->id);
                if (! $taller) {
                    continue;
                }

                $clase = $clases->get($taller->id.'|'.$r['fecha']);
                if (! $clase) {
                    // Fecha que el calendario no tenia (ej. dia 29-31, o una
                    // clase de recuperacion): se crea con la hora del horario.
                    $dia = \Carbon\Carbon::parse($r['fecha'])->isoWeekday();
                    $horario = $taller->horarios->firstWhere('dia_semana', $dia) ?? $taller->horarios->first();
                    if (! $horario) {
                        continue;
                    }
                    $clase = Clase::create([
                        'horario_id' => $horario->dia_semana == $dia ? $horario->id : null,
                        'alumno_taller_id' => $taller->id,
                        'periodo_id' => $periodo->id,
                        'alumno_id' => $alumno->id,
                        'maestro_id' => $taller->maestro_id,
                        'especialidad_id' => $taller->especialidad_id,
                        'fecha' => $r['fecha'],
                        'hora_inicio' => $horario->hora_inicio,
                        'hora_fin' => $horario->hora_fin,
                        'estado' => 'programada',
                    ]);
                    $clases->put($taller->id.'|'.$r['fecha'], $clase);
                }

                if ($yaMarcadas->has($clase->id)) {
                    continue;
                }
                $yaMarcadas->put($clase->id, true);

                $nuevas[] = [
                    'clase_id' => $clase->id,
                    'alumno_id' => $alumno->id,
                    'estado' => $r['estado'],
                    'observacion' => $r['observacion'],
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
                // Igual que al marcar desde la app: la clase pasa a "realizada".
                if ($clase->estado === 'programada') {
                    $realizadas[] = $clase->id;
                }
            }

            foreach (array_chunk($nuevas, 500) as $lote) {
                DB::table('asistencias')->insert($lote);
            }
            foreach (array_chunk($realizadas, 500) as $lote) {
                Clase::whereIn('id', $lote)->update(['estado' => 'realizada']);
            }
        });
    }

    public function down(): void
    {
        // Carga de datos: no se revierte automaticamente.
    }

    private function clave(string $nombre): string
    {
        return Str::upper(preg_replace('/\s+/', ' ', trim(Str::ascii($nombre))));
    }
};
