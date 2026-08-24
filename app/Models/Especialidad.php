<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'color', 'precio_mensual', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }

    public function maestros()
    {
        return $this->belongsToMany(Maestro::class, 'especialidad_maestro')->withPivot('tarifa_hora');
    }
}
