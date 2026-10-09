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
        'veces_semana', 'monto_mensual', 'nota_mensualidad',
    ];

    /** Nota de la mensualidad antes del ultimo cambio (para actualizar la observacion del pago). */
    protected ?string $notaPrevia = null;

    protected static function booted(): void
    {
        static::updating(function (self $taller) {
            if ($taller->isDirty('nota_mensualidad')) {
                $taller->notaPrevia = $taller->getOriginal('nota_mensualidad');
            }
        });
    }

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

        $nota = trim((string) ($data['nota_mensualidad'] ?? ''));

        return [
            'veces_semana' => $veces ? (int) $veces : null,
            'monto_mensual' => ($monto === null || $monto === '') ? null : number_format((float) $monto, 2, '.', ''),
            'nota_mensualidad' => $nota !== '' ? $nota : null,
        ];
    }

    /**
     * Da de baja el taller sin borrar su historial: queda inactivo, sus
     * horarios dejan de generar clases, se cancelan las clases que aun no
     * se dictaron (programadas y sin asistencia marcada) y, si no llego a
     * tener ninguna clase, se elimina su mensualidad sin abonos (por
     * ejemplo, si se paso de periodo por error). Lo ya dictado y lo ya
     * pagado se conserva, y si estudio parte del mes su deuda se mantiene.
     */
    public function darDeBaja(): void
    {
        $this->update(['estado' => 'inactivo']);
        $this->horarios()->update(['activo' => false]);
        $this->clases()->where('estado', 'programada')->whereDoesntHave('asistencia')
            ->update(['estado' => 'cancelada']);

        $tuvoClases = $this->clases()->where(fn ($q) => $q->where('estado', 'realizada')->orWhereHas('asistencia'))->exists();
        if (! $tuvoClases) {
            $this->pagos()->whereDoesntHave('abonos')->delete();
        }
    }

    /** Tiene historial que no se debe borrar: asistencias marcadas o abonos. */
    public function tieneHistorial(): bool
    {
        return $this->clases()->where(fn ($q) => $q->where('estado', 'realizada')->orWhereHas('asistencia'))->exists()
            || $this->pagos()->whereHas('abonos')->exists();
    }

    /**
     * Elimina por completo un taller dado de baja que no tiene historial
     * (por ejemplo, uno creado por error o repetido): sus horarios, clases
     * y mensualidad sin abonos.
     */
    public function eliminarDefinitivo(): void
    {
        $this->clases()->delete();
        $this->horarios()->get()->each->deleteQuietly();
        $this->pagos()->whereDoesntHave('abonos')->delete();
        $this->delete();
    }

    /**
     * Revierte una baja: el taller vuelve a estar activo con sus horarios,
     * las clases canceladas que faltan dictar vuelven a programarse y se
     * recrea su mensualidad.
     */
    public function reactivar(): void
    {
        $this->update(['estado' => 'activo']);
        $this->horarios()->update(['activo' => true]);
        $this->clases()->where('estado', 'cancelada')->whereDate('fecha', '>=', now()->toDateString())
            ->update(['estado' => 'programada']);
        $this->sincronizarPago();
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

        // La nota de la mensualidad pasa a la observacion del pago, salvo que
        // alli ya se haya escrito otra cosa a mano.
        $notaAnterior = $this->notaPrevia ?? $this->getOriginal('nota_mensualidad');
        $automatica = blank($pago->observacion) || str_starts_with($pago->observacion, 'Importado de ');
        $observacion = $automatica || $pago->observacion === $notaAnterior || $pago->observacion === $this->nota_mensualidad
            ? $this->nota_mensualidad
            : $pago->observacion;

        if ($pago->exists && (float) $pago->monto_total === (float) $this->monto_mensual && $pago->observacion === $observacion) {
            return $pago;
        }

        $pago->fill([
            'alumno_id' => $this->alumno_id,
            'concepto' => $pago->concepto ?: 'Mensualidad '.($this->especialidad->nombre ?? ''),
            'monto_total' => $this->monto_mensual,
            'observacion' => $observacion,
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