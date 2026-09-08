<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Un abono es un movimiento de dinero individual dentro de un Pago
 * (seccion 10 del requerimiento). Un mismo Pago (deuda) puede tener
 * varios abonos: cada uno con su propia fecha, metodo de pago y
 * numero de recibo. Nunca se recalculan/combinan: quedan como
 * historial permanente de cada abono realizado.
 */
class PagoAbono extends Model
{
    use HasFactory;

    protected $table = 'pago_abonos';

    protected $fillable = [
        'pago_id', 'monto', 'fecha', 'metodo_pago', 'recibo_nro', 'registrado_por', 'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    const METODOS = [
        'transferencia' => 'Transferencia',
        'yape' => 'Yape',
        'efectivo' => 'Efectivo',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function metodoLabel(): string
    {
        return self::METODOS[$this->metodo_pago] ?? $this->metodo_pago;
    }
}
