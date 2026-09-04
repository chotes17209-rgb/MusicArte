<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maestro extends Model
{
    use HasFactory;

    protected $table = 'maestros';

    protected $fillable = [
        'nombre', 'telefono', 'email', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /** Un maestro puede ensenar varias especialidades (Piano y Bateria, por ejemplo). */
    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_maestro')->withPivot('tarifa_hora');
    }

    /** Tarifa por hora que se le paga a este maestro por una especialidad especifica. */
    public function tarifaHoraPara(int $especialidadId): float
    {
        $pivot = $this->especialidades->firstWhere('id', $especialidadId);

        return $pivot ? (float) $pivot->pivot->tarifa_hora : 0;
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    /** Lista de nombres de especialidades separadas por coma, para mostrar en tablas. */
    public function especialidadesLabel(): string
    {
        return $this->especialidades->pluck('nombre')->join(', ') ?: '—';
    }
}
