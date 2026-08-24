<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaChica extends Model
{
    use HasFactory;

    protected $table = 'caja_chica';

    protected $fillable = ['fecha', 'proveedor', 'descripcion', 'monto'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }
}
