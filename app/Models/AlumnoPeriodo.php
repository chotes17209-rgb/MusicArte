<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Historial de actividad de un alumno por periodo (seccion 7 del
 * requerimiento). Una fila = "este alumno estuvo activo o inactivo en
 * este mes/periodo". Nunca se borra al pasar de un periodo a otro.
 */
class AlumnoPeriodo extends Model
{
    use HasFactory;

    protected $table = 'alumno_periodo';

    protected $fillable = ['alumno_id', 'periodo_id', 'estado'];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}