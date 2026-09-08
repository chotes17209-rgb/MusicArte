<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Inscripcion de un alumno a un taller (especialidad) especifico.
 * Un alumno puede tener varias filas de este modelo al mismo tiempo,
 * por ejemplo: Piano con Juan los lunes, y Canto con Maria los miercoles.
 */
class AlumnoTaller extends Model
{
    use HasFactory;

    protected $table = 'alumno_talleres';

    protected $fillable = [
        'alumno_id', 'especialidad_id', 'maestro_id', 'periodo_id', 'salon', 'estado',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}