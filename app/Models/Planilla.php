<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planilla extends Model
{
    use HasFactory;

    protected $table = 'planillas';

    protected $fillable = [
        'maestro_id', 'alumno_id', 'mes', 'anio', 'especialidad_id', 'horas', 'monto', 'observacion',
    ];

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function mesLabel(): string
    {
        return Pago::MESES[$this->mes] ?? '';
    }
}
