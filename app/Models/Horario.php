<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'alumno_id', 'maestro_id', 'especialidad_id', 'periodo_id',
        'dia_semana', 'hora_inicio', 'hora_fin', 'salon', 'activo', 'estado',
    ];

    const ESTADOS = [
        'activo' => 'Activo',
        'pausado' => 'Pausado',
        'finalizado' => 'Finalizado',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    const DIAS = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo',
    ];

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
}
