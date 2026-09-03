<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoAbono extends Model
{
    use HasFactory;

    protected $table = 'pago_abonos';

    protected $fillable = [
        'pago_id', 'monto', 'fecha', 'metodo_pago', 'recibo_nro', 'usuario_id',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
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

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    public function metodoLabel(): string
    {
        return self::METODOS[$this->metodo_pago] ?? ucfirst($this->metodo_pago);
    }

    /**
     * Req. 10 y 11: el saldo del Pago se recalcula solo, automaticamente,
     * cada vez que se crea, edita o borra un abono. Asi nunca queda
     * desincronizado sin importar desde donde se registre el pago.
     */
    protected static function booted(): void
    {
        static::created(fn (PagoAbono $abono) => $abono->pago?->recalcular());
        static::updated(fn (PagoAbono $abono) => $abono->pago?->recalcular());
        static::deleted(fn (PagoAbono $abono) => $abono->pago?->recalcular());
    }
}
