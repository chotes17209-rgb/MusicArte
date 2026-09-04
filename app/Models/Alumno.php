<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumnos';

    protected $fillable = [
        'nombre', 'fecha_nacimiento', 'especialidad_id', 'maestro_id',
        'tutor', 'celular', 'dni', 'diagnostico',
        'fecha_ingreso', 'activo', 'observaciones',
    ];

    /**
     * Edad calculada automaticamente desde fecha_nacimiento (req. 2.1).
     * Ya no existe columna "edad": esto siempre refleja la edad real,
     * considerando si ya cumplio anos o no este ano.
     */
    protected function edad(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->fecha_nacimiento
                ? Carbon::parse($this->fecha_nacimiento)->age
                : null,
        );
    }

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
        ];
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function maestro()
    {
        return $this->belongsTo(Maestro::class);
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

    /** Filtra alumnos que tienen algun taller (horario) con ese maestro (req. 1.2). */
    public function scopeDelMaestro($query, int $maestroId)
    {
        return $query->whereHas('horarios', function ($q) use ($maestroId) {
            $q->where('maestro_id', $maestroId);
        });
    }

    /** Filtra alumnos con algun taller (horario) dentro de ese periodo (req. 1.1). */
    public function scopeDelPeriodo($query, int $periodoId)
    {
        return $query->whereHas('horarios', function ($q) use ($periodoId) {
            $q->where('periodo_id', $periodoId);
        });
    }

    /**
     * Talleres unicos del alumno (req. 3 y 4): agrupa sus horarios por
     * especialidad+maestro+periodo para que cada taller se vea como un bloque
     * independiente, aunque tenga varios dias/horas dentro de ese mismo taller.
     */
    public function talleres()
    {
        return $this->horarios()
            ->with(['especialidad', 'maestro', 'periodo'])
            ->orderBy('dia_semana')
            ->get()
            ->groupBy(fn ($h) => $h->especialidad_id.'-'.$h->maestro_id.'-'.$h->periodo_id)
            ->map(function ($grupo) {
                $primero = $grupo->first();

                return (object) [
                    'especialidad' => $primero->especialidad,
                    'maestro' => $primero->maestro,
                    'periodo' => $primero->periodo,
                    'estado' => $primero->estado ?? ($primero->activo ? 'activo' : 'pausado'),
                    'horarios' => $grupo->values(),
                ];
            })
            ->values();
    }

    /** Texto "Piano, Canto" para listados donde antes solo se mostraba una especialidad. */
    public function talleresLabel(): string
    {
        return $this->talleres()
            ->map(fn ($t) => $t->especialidad->nombre ?? '—')
            ->unique()
            ->implode(', ') ?: '—';
    }

    /** Texto "Juan Perez, Maria Lopez" con los maestros de todos sus talleres. */
    public function maestrosLabel(): string
    {
        return $this->talleres()
            ->map(fn ($t) => $t->maestro->nombre ?? null)
            ->filter()
            ->unique()
            ->implode(', ') ?: '—';
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
}
