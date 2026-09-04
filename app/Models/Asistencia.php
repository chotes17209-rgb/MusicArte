<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'clase_id', 'alumno_id', 'estado', 'observacion', 'registrado_por',
    ];

    public function clase()
    {
        return $this->belongsTo(Clase::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
