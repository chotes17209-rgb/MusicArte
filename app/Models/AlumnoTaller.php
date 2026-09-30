<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Inscripcion de un alumno a un taller (especialidad) especifico.
 * Un alumno puede tener varias filas de este modelo al mismo tiempo,
 * por ejemplo: Piano con Juan los lunes, y Canto con Maria los miercoles.
 */
class AlumnoTaller extends Model
{
    use HasFactory;

    protected $table = 'alumno_talleres';

    protected $fillable = [
        'alumno_id', 'especialidad_id', 'maestro_id', 'periodo_id', 'salon', 'estado',
        'veces_semana', 'monto_mensual',
    ];

    protected function casts(): array
    {
        return ['veces_semana' => 'integer', 'monto_mensual' => 'decimal:2'];
    }

    public function modalidadLabel(): ?string
    {
        if (! $this->veces_semana) {
            return null;
        }

        return $this->veces_semana === 1 ? '1 vez por semana' : "{$this->veces_semana} veces por semana";
    }

    /**
     * Modalidad y mensualidad a guardar: la mensualidad es la que se
     * escribe en el formulario; si no se elige la modalidad, se deduce de
     * los dias de horario marcados.
     */
    public static function resolverMensualidad(array $data, ?array $horarios = null): array
    {
        $veces = $data['veces_semana'] ?? null;
        if (! $veces && $horarios) {
            $veces = collect($horarios)->pluck('dia_semana')->unique()->count() ?: null;
        }

        $monto = $data['monto_mensual'] ?? null;

        return [
            'veces_semana' => $veces ? (int) $veces : null,
            'monto_mensual' => ($monto === null || $monto === '') ? null : round((float) $monto, 2),
        ];
    }

    /**
     * Deja el pago del mes de este taller igual a su mensualidad: si no
     * existe lo crea (pendiente) y si cambio el monto lo actualiza, sin
     * tocar los abonos ya registrados. Solo aplica a talleres activos,
     * con periodo y con monto definido.
     */
    public function sincronizarPago(): ?Pago
    {
        if ($this->estado !== 'activo' || ! $this->periodo_id || $this->monto_mensual === null) {
            return null;
        }

        $periodo = $this->periodo;
        $pago = Pago::firstOrNew([
            'alumno_taller_id' => $this->id,
            'mes' => $periodo->mes,
            'anio' => $periodo->anio,
        ]);

        if ($pago->exists && (float) $pago->monto_total === (float) $this->monto_mensual) {
            return $pago;
        }

        $pago->fill([
            'alumno_id' => $this->alumno_id,
            'concepto' => $pago->concepto ?: 'Mensualidad '.($this->especialidad->nombre ?? ''),
            'monto_total' => $this->monto_mensual,
        ]);

        if (! $pago->exists) {
            $pago->saldo = $this->monto_mensual;
            $pago->estado = $this->monto_mensual > 0 ? 'pendiente' : 'pagado';
            $pago->save();

            return $pago;
        }

        $pago->save();
        $pago->recalcular();

        return $pago;
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}