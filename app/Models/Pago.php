<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'alumno_id', 'mes', 'anio', 'concepto', 'monto_total',
        'yape_transferencia', 'efectivo', 'tarjeta', 'saldo',
        'recibo_nro', 'fecha_pago', 'observacion',
    ];

    protected function casts(): array
    {
        return ['fecha_pago' => 'date'];
    }

    const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function mesLabel(): string
    {
        return self::MESES[$this->mes] ?? '';
    }

    public function scopePendientes($query)
    {
        return $query->where('saldo', '>', 0);
    }
}
