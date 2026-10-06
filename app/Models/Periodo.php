<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Periodo extends Model
{
    use HasFactory;

    protected $table = 'periodos';

    protected $fillable = [
        'nombre', 'mes', 'anio', 'fecha_inicio', 'fecha_fin', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
            'cerrado_en' => 'datetime',
        ];
    }

    /** Ya paso su fecha de fin. */
    public function haTerminado(): bool
    {
        return $this->fecha_fin->lt(now()->startOfDay());
    }

    /** Termino: paso su fecha de fin o se cerro (al pasar sus alumnos al siguiente periodo). */
    public function finalizado(): bool
    {
        return $this->haTerminado() || (! $this->activo && $this->cerrado_en !== null);
    }

    /** Periodos donde hoy se puede estudiar: activos y que aun no terminan. */
    public function scopeVigentes($query)
    {
        return $query->where('activo', true)->whereDate('fecha_fin', '>=', now()->toDateString());
    }

    /**
     * Cierra el periodo: queda inactivo (solo historial) y sus alumnos que
     * no continuan en otro periodo vigente quedan inactivos.
     */
    public function cerrar(): void
    {
        DB::transaction(function () {
            $this->forceFill(['activo' => false, 'cerrado_en' => now()])->save();

            $alumnosDelPeriodo = AlumnoTaller::where('periodo_id', $this->id)
                ->where('estado', 'activo')->distinct()->pluck('alumno_id');
            $noContinuan = $alumnosDelPeriodo->diff(static::alumnosConPeriodoVigente($alumnosDelPeriodo));

            Alumno::whereIn('id', $noContinuan)->update(['activo' => false]);
        });
    }

    /**
     * Cierra los periodos cuya fecha de fin ya paso. Cada periodo se cierra
     * una sola vez (cerrado_en), asi que si se reabre a mano no se vuelve a
     * cerrar.
     *
     * @return int cantidad de periodos cerrados
     */
    public static function cerrarVencidos(): int
    {
        $vencidos = static::where('activo', true)->whereNull('cerrado_en')
            ->whereDate('fecha_fin', '<', now()->toDateString())
            ->get();

        $vencidos->each->cerrar();

        return $vencidos->count();
    }

    /** De estos alumnos, los que tienen un taller activo en un periodo vigente. */
    public static function alumnosConPeriodoVigente(Collection $alumnoIds): Collection
    {
        return AlumnoTaller::whereIn('alumno_id', $alumnoIds)->where('estado', 'activo')
            ->whereHas('periodo', fn ($q) => $q->vigentes())
            ->distinct()->pluck('alumno_id');
    }

    /**
     * A que periodo siguiente paso cada alumno: el primero posterior donde
     * tiene un taller activo o quedo activo al pasarlo.
     *
     * @return Collection<int, string> alumno_id => nombre del periodo
     */
    public function siguientePeriodoDe(Collection $alumnoIds): Collection
    {
        $posteriores = static::where('fecha_inicio', '>', $this->fecha_inicio)->orderBy('fecha_inicio')->get()->keyBy('id');
        if ($posteriores->isEmpty()) {
            return collect();
        }

        // Un alumno paso a un periodo posterior si tiene ahi un taller activo
        // o si se le marco activo al pasarlo (aunque no se copiaran talleres).
        $porTaller = AlumnoTaller::whereIn('alumno_id', $alumnoIds)->where('estado', 'activo')
            ->whereIn('periodo_id', $posteriores->keys())->get(['alumno_id', 'periodo_id']);
        $porPase = AlumnoPeriodo::whereIn('alumno_id', $alumnoIds)->where('estado', 'activo')
            ->whereIn('periodo_id', $posteriores->keys())->get(['alumno_id', 'periodo_id']);

        return $porTaller->concat($porPase)
            ->sortBy(fn ($r) => $posteriores[$r->periodo_id]->fecha_inicio)
            ->unique('alumno_id')
            ->mapWithKeys(fn ($r) => [$r->alumno_id => $posteriores[$r->periodo_id]->nombre]);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function clases()
    {
        return $this->hasMany(Clase::class);
    }

    /**
     * Periodo en curso segun sus fechas (hoy cae entre fecha_inicio y
     * fecha_fin). Si hoy cae en el hueco entre dos periodos, se toma el
     * siguiente que empieza; si no hay ninguno, el activo mas reciente.
     */
    public static function enCurso(): ?self
    {
        $hoy = now()->toDateString();

        // Primero los periodos abiertos: uno cerrado antes de tiempo (al pasar
        // sus alumnos) cede su lugar al siguiente.
        return static::where('activo', true)->whereDate('fecha_inicio', '<=', $hoy)->whereDate('fecha_fin', '>=', $hoy)->orderBy('fecha_inicio')->first()
            ?? static::where('activo', true)->whereDate('fecha_inicio', '>', $hoy)->orderBy('fecha_inicio')->first()
            ?? static::whereDate('fecha_inicio', '<=', $hoy)->whereDate('fecha_fin', '>=', $hoy)->orderBy('fecha_inicio')->first()
            ?? static::whereDate('fecha_inicio', '>', $hoy)->orderBy('fecha_inicio')->first()
            ?? static::where('activo', true)->orderByDesc('anio')->orderByDesc('mes')->first()
            ?? static::orderByDesc('anio')->orderByDesc('mes')->first();
    }

    /**
     * Periodo con el que se esta trabajando en toda la app: el elegido en
     * la barra superior (se guarda en la sesion) o, si no se eligio, el
     * periodo en curso. Todas las pantallas lo usan como filtro por defecto.
     */
    public static function seleccionado(): ?self
    {
        static $cache = [];
        $id = session('periodo_id');
        $clave = $id ?: 'en-curso';

        if (! array_key_exists($clave, $cache)) {
            $cache[$clave] = ($id ? static::find($id) : null) ?? static::enCurso();
        }

        return $cache[$clave];
    }

    /** [mes, anio] del periodo seleccionado (o del mes actual si no hay periodos). */
    public static function mesAnioSeleccionado(): array
    {
        $p = static::seleccionado();

        return $p ? [(int) $p->mes, (int) $p->anio] : [(int) now()->month, (int) now()->year];
    }

    public function estaEnCurso(): bool
    {
        return now()->startOfDay()->between($this->fecha_inicio, $this->fecha_fin);
    }

    /** Cuantas semanas dura el periodo, para mostrarlo en pantalla. */
    public function duracionSemanas(): int
    {
        return (int) ceil(($this->fecha_inicio->diffInDays($this->fecha_fin) + 1) / 7);
    }

    /** Sugiere fecha_inicio/fecha_fin de 4 semanas (28 dias) para un mes/anio. */
    public static function sugerirRango(int $mes, int $anio): array
    {
        $inicio = Carbon::create($anio, $mes, 1);
        $fin = $inicio->copy()->addDays(27);

        return [$inicio->toDateString(), $fin->toDateString()];
    }
}