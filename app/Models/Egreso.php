<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egresos';

    protected $fillable = [
        'fecha', 'detalle', 'yape_bcp', 'plin_ibk', 'tarjeta', 'efectivo', 'total',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }
}
