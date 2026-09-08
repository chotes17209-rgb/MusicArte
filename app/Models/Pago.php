<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'alumno_id', 'alumno_taller_id', 'mes', 'anio', 'concepto', 'monto_total',
        'yape_transferencia', 'efectivo', 'tarjeta', 'saldo', 'estado',
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

    /** Estados posibles de un pago (seccion 11). */
    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'a_cuenta' => 'A cuenta',
        'pagado' => 'Pagado',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    /** Taller especifico (de los varios que puede tener el alumno) al que corresponde este pago. */
    public function alumnoTaller()
    {
        return $this->belongsTo(AlumnoTaller::class);
    }

    /** Todos los abonos/movimientos de dinero de este pago (seccion 10). */
    public function abonos()
    {
        return $this->hasMany(PagoAbono::class)->orderBy('fecha')->orderBy('id');
    }

    public function mesLabel(): string
    {
        return self::MESES[$this->mes] ?? '';
    }

    public function estadoLabel(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function scopePendientes($query)
    {
        return $query->where('saldo', '>', 0);
    }

    /** Cuanto se ha abonado en total hasta ahora (suma de todos los abonos). */
    public function montoAbonado(): float
    {
        return (float) $this->abonos()->sum('monto');
    }

    /**
     * Recalcula saldo y estado a partir de los abonos reales (seccion 10 y
     * 11). Se llama cada vez que se crea, edita o elimina un abono, o
     * cuando cambia el monto_total del pago. El saldo NUNCA se edita a
     * mano: siempre es monto_total menos lo abonado.
     */
    public function recalcular(): void
    {
        $abonado = $this->montoAbonado();
        $saldo = max(0, round($this->monto_total - $abonado, 2));

        $estado = 'pendiente';
        if ($saldo <= 0 && $this->monto_total > 0) {
            $estado = 'pagado';
        } elseif ($abonado > 0) {
            $estado = 'a_cuenta';
        }

        $this->forceFill([
            'saldo' => $saldo,
            'estado' => $estado,
            // Los campos legado (yape/efectivo/tarjeta) reflejan la suma de
            // los abonos por metodo, solo para no romper reportes viejos.
            'yape_transferencia' => (float) $this->abonos()->where('metodo_pago', 'transferencia')->sum('monto'),
            'efectivo' => (float) $this->abonos()->where('metodo_pago', 'efectivo')->sum('monto'),
            'tarjeta' => (float) $this->abonos()->where('metodo_pago', 'yape')->sum('monto'),
            'fecha_pago' => optional($this->abonos()->latest('fecha')->first())->fecha ?? $this->fecha_pago,
        ])->save();
    }
}
