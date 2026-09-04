<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumnos';

    protected $fillable = [
        'nombre', 'edad', 'fecha_nacimiento', 'especialidad_id', 'maestro_id',
        'tutor', 'celular', 'dni', 'diagnostico', 'direccion',
        'fecha_ingreso', 'activo', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
        ];
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /** Saldo pendiente de pago del mes/anio actual, para alertas del dashboard. */
    public function saldoPendienteMesActual(): float
    {
        $pago = $this->pagos()
            ->where('mes', now()->month)
            ->where('anio', now()->year)
            ->first();

        return $pago ? (float) $pago->saldo : (float) 0;
    }
}
