<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'alumno_id', 'periodo_id', 'especialidad_id', 'maestro_id',
        'mes', 'anio', 'concepto', 'monto_total', 'monto_pagado', 'saldo',
        'estado', 'usuario_id', 'recibo_nro', 'fecha_pago', 'observacion',
        // Campos legados (se mantienen solo por compatibilidad con datos historicos):
        'yape_transferencia', 'efectivo', 'tarjeta',
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

    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'a_cuenta' => 'A cuenta',
        'pagado' => 'Pagado',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    public function abonos()
    {
        return $this->hasMany(PagoAbono::class)->orderBy('fecha');
    }

    public function mesLabel(): string
    {
        return self::MESES[$this->mes] ?? ($this->periodo->nombre ?? '');
    }

    public function estadoLabel(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    /**
     * Req. 10 y 11: recalcula monto_pagado, saldo y estado a partir de la
     * suma real de abonos. Se llama automaticamente cada vez que se
     * crea/edita/borra un abono (ver PagoAbono::booted()), y tambien cuando
     * se corrige el monto_total de la deuda.
     */
    public function recalcular(): void
    {
        $pagado = (float) $this->abonos()->sum('monto');
        $saldo = max(0, (float) $this->monto_total - $pagado);

        $estado = 'pendiente';
        if ($pagado > 0 && $saldo > 0) {
            $estado = 'a_cuenta';
        } elseif ($pagado > 0 && $saldo <= 0) {
            $estado = 'pagado';
        }

        $this->forceFill([
            'monto_pagado' => $pagado,
            'saldo' => $saldo,
            'estado' => $estado,
        ])->saveQuietly();
    }

    public function scopePendientes($query)
    {
        return $query->where('saldo', '>', 0);
    }

    public function scopeDelPeriodo($query, int $periodoId)
    {
        return $query->where('periodo_id', $periodoId);
    }

    public function scopeDelMaestro($query, int $maestroId)
    {
        return $query->where('maestro_id', $maestroId);
    }

    public function scopeDelTaller($query, int $especialidadId)
    {
        return $query->where('especialidad_id', $especialidadId);
    }
}
