<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        ];
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

        return static::whereDate('fecha_inicio', '<=', $hoy)->whereDate('fecha_fin', '>=', $hoy)->orderBy('fecha_inicio')->first()
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