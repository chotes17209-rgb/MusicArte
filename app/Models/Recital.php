<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recital extends Model
{
    use HasFactory;

    protected $table = 'recitales';

    protected $fillable = [
        'nombre', 'fecha', 'tema', 'descripcion', 'participantes', 'pago_por_alumno',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }
}
