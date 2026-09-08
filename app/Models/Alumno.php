<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumnos';

    protected $fillable = [
        'nombre', 'edad', 'fecha_nacimiento', 'especialidad_id', 'maestro_id',
        'tutor', 'celular', 'dni', 'diagnostico', 'direccion',
        'fecha_ingreso', 'activo', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
        ];
    }

    /**
     * NOTA (Fase 2 - multiples talleres): especialidad_id y maestro_id se
     * mantienen como el "taller principal" del alumno, solo por
     * compatibilidad con modulos que todavia no fueron migrados a trabajar
     * con multiples talleres (Pagos, Asistencia, Reportes). Se actualizan
     * automaticamente via sincronizarTallerPrincipal() y ya NO se editan a
     * mano desde el formulario de alumnos. La fuente de verdad para saber
     * los talleres de un alumno es la relacion talleres().
     */
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
    }

    /** Todos los talleres del alumno (activos e inactivos, para su historial). */
    public function talleres()
    {
        return $this->hasMany(AlumnoTaller::class);
    }

    /** Solo los talleres en los que el alumno esta activo actualmente. */
    public function talleresActivos()
    {
        return $this->hasMany(AlumnoTaller::class)->where('estado', 'activo');
    }

    /** Historial de activo/inactivo por periodo (seccion 6 y 7). */
    public function historialPeriodos()
    {
        return $this->hasMany(AlumnoPeriodo::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /** Saldo pendiente de pago del mes/anio actual, para alertas del dashboard. */
    public function saldoPendienteMesActual(): float
    {
        $pago = $this->pagos()
            ->where('mes', now()->month)
            ->where('anio', now()->year)
            ->first();

        return $pago ? (float) $pago->saldo : (float) 0;
    }

    /**
     * Mantiene sincronizados especialidad_id / maestro_id (el "taller
     * principal" legado) con el primer taller activo del alumno. Se llama
     * automaticamente cada vez que se crea, edita o quita un taller.
     */
    public function sincronizarTallerPrincipal(): void
    {
        $principal = $this->talleres()->where('estado', 'activo')->oldest('id')->first();

        $this->forceFill([
            'especialidad_id' => $principal->especialidad_id ?? null,
            'maestro_id' => $principal->maestro_id ?? null,
        ])->saveQuietly();
    }

    /**
     * Mantiene sincronizado alumno_periodo para un periodo especifico: el
     * alumno queda "activo" en ese periodo si tiene al menos un taller
     * activo con ese periodo asignado; si no, queda "inactivo". Se llama
     * automaticamente cada vez que se crea, edita o quita un taller con
     * periodo. Nunca elimina el registro, solo actualiza su estado, para
     * no perder el historial (regla 4 del requerimiento).
     */
    public function sincronizarEstadoPeriodo(?int $periodoId): void
    {
        if (! $periodoId) {
            return;
        }

        $activo = $this->talleres()
            ->where('periodo_id', $periodoId)
            ->where('estado', 'activo')
            ->exists();

        AlumnoPeriodo::updateOrCreate(
            ['alumno_id' => $this->id, 'periodo_id' => $periodoId],
            ['estado' => $activo ? 'activo' : 'inactivo']
        );
    }
}