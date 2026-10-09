<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'alumno_id', 'alumno_taller_id', 'maestro_id', 'especialidad_id', 'periodo_id',
        'dia_semana', 'hora_inicio', 'hora_fin', 'salon', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    const DIAS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    /** El taller especifico (de los varios que puede tener el alumno) al que pertenece este horario. */
    public function alumnoTaller()
    {
        return $this->belongsTo(AlumnoTaller::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    public function diaLabel(): string
    {
        return self::DIAS[$this->dia_semana] ?? '';
    }

    /**
     * Las clases del calendario siguen al horario. Si se cambia el dia, se
     * desactiva o se elimina, sus clases pendientes (las del periodo que aun
     * no tienen asistencia marcada) se quitan y, si sigue activo, se generan
     * las del dia nuevo en todo su periodo. Si solo cambia la hora, el maestro o el salon, las clases
     * pendientes se actualizan. Lo ya dictado o marcado no se toca.
     */
    protected static function booted(): void
    {
        static::updated(function (self $horario) {
            $cambioDia = $horario->wasChanged(['dia_semana', 'periodo_id']);

            if (! $horario->activo || $cambioDia) {
                $horario->quitarClasesPendientes();
            } elseif ($horario->wasChanged(['hora_inicio', 'hora_fin', 'maestro_id', 'especialidad_id', 'salon'])) {
                // Las que siguen con la hora anterior (una clase movida a mano se respeta).
                $horario->clasesPendientes()
                    ->where('hora_inicio', $horario->getOriginal('hora_inicio'))
                    ->update(['hora_inicio' => $horario->hora_inicio, 'hora_fin' => $horario->hora_fin]);
                $horario->clasesPendientes()->update([
                    'maestro_id' => $horario->maestro_id,
                    'especialidad_id' => $horario->especialidad_id,
                    'salon' => $horario->salon,
                ]);
            }

            if ($horario->activo && ($cambioDia || $horario->wasChanged('activo'))) {
                $horario->generarClases();
            }
        });

        static::deleting(fn (self $horario) => $horario->quitarClasesPendientes());
    }

    /**
     * Clases del horario que aun no tienen asistencia ni se marcaron como
     * dictadas (de todo el periodo, tambien dias pasados: la asistencia de un
     * mes se suele marcar despues).
     */
    public function clasesPendientes()
    {
        return $this->clases()->where('estado', 'programada')->whereDoesntHave('asistencia');
    }

    public function quitarClasesPendientes(): int
    {
        return $this->clasesPendientes()->delete();
    }

    /**
     * Crea en el calendario las clases que faltan de este horario, siempre
     * DENTRO de su periodo (un horario de setiembre nunca genera clases en
     * octubre). Opcionalmente acotado a un rango [desde, hasta].
     *
     * @return int cantidad de clases creadas
     */
    public function generarClases(?Carbon $desde = null, ?Carbon $hasta = null): int
    {
        $periodo = $this->periodo;
        if (! $periodo || ! $this->activo) {
            return 0;
        }

        $inicio = $periodo->fecha_inicio->copy();
        if ($desde && $desde->copy()->startOfDay()->gt($inicio)) {
            $inicio = $desde->copy()->startOfDay();
        }
        $fin = $periodo->fecha_fin->copy();
        if ($hasta && $hasta->copy()->startOfDay()->lt($fin)) {
            $fin = $hasta->copy()->startOfDay();
        }

        // Los dias que ya son del periodo siguiente del alumno no se generan aqui.
        if ($this->alumno_id) {
            $siguiente = Periodo::whereDate('fecha_inicio', '>', $periodo->fecha_inicio->toDateString())
                ->whereIn('id', AlumnoTaller::where('alumno_id', $this->alumno_id)->where('estado', 'activo')->whereNotNull('periodo_id')->select('periodo_id'))
                ->min('fecha_inicio');
            if ($siguiente && Carbon::parse($siguiente)->startOfDay()->lte($fin)) {
                $fin = Carbon::parse($siguiente)->startOfDay()->subDay();
            }
        }

        $existentes = $this->clases()->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->pluck('fecha')->map(fn ($f) => Carbon::parse($f)->toDateString())->flip();

        $creadas = 0;
        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            if ($fecha->isoWeekday() != $this->dia_semana || isset($existentes[$fecha->toDateString()])) {
                continue;
            }

            Clase::create([
                'horario_id' => $this->id,
                'alumno_taller_id' => $this->alumno_taller_id,
                'alumno_id' => $this->alumno_id,
                'maestro_id' => $this->maestro_id,
                'especialidad_id' => $this->especialidad_id,
                'periodo_id' => $periodo->id,
                'fecha' => $fecha->toDateString(),
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
                'salon' => $this->salon,
                'estado' => 'programada',
            ]);
            $creadas++;
        }

        return $creadas;
    }
}