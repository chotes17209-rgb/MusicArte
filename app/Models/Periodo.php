<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Periodo extends Model
{
    use HasFactory;

    protected $table = 'periodos';

    protected $fillable = [
        'nombre', 'mes', 'anio', 'fecha_inicio', 'fecha_fin', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    /** Cuantas semanas dura el periodo, para mostrarlo en pantalla. */
    public function duracionSemanas(): int
    {
        return (int) ceil(($this->fecha_inicio->diffInDays($this->fecha_fin) + 1) / 7);
    }

    /** Sugiere fecha_inicio/fecha_fin de 4 semanas (28 dias) para un mes/anio. */
    public static function sugerirRango(int $mes, int $anio): array
    {
        $inicio = Carbon::create($anio, $mes, 1);
        $fin = $inicio->copy()->addDays(27);

        return [$inicio->toDateString(), $fin->toDateString()];
    }
}