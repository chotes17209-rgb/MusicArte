<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    use HasFactory;

    protected $table = 'clases';

    protected $fillable = [
        'horario_id', 'alumno_id', 'maestro_id', 'especialidad_id', 'periodo_id',
        'fecha', 'hora_inicio', 'hora_fin', 'salon', 'estado', 'notas',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function asistencia()
    {
        return $this->hasOne(Asistencia::class);
    }

    /** Representacion como evento para FullCalendar. */
    public function toCalendarEvent(): array
    {
        $colores = [
            'programada' => $this->especialidad->color ?? '#3d2c8d',
            'realizada' => '#2e7d32',
            'cancelada' => '#b71c1c',
        ];

        return [
            'id' => $this->id,
            'title' => $this->alumno->nombre.' - '.($this->especialidad->nombre ?? ''),
            'start' => $this->fecha->format('Y-m-d').'T'.$this->hora_inicio,
            'end' => $this->fecha->format('Y-m-d').'T'.$this->hora_fin,
            'backgroundColor' => $colores[$this->estado] ?? '#3d2c8d',
            'borderColor' => $colores[$this->estado] ?? '#3d2c8d',
            'extendedProps' => [
                'alumno' => $this->alumno->nombre,
                'maestro' => $this->maestro->nombre ?? 'Sin asignar',
                'especialidad' => $this->especialidad->nombre ?? '',
                'salon' => $this->salon,
                'estado' => $this->estado,
                'notas' => $this->notas,
                'asistencia' => $this->asistencia->estado ?? null,
            ],
        ];
    }
}
