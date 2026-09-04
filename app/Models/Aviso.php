<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    use HasFactory;

    protected $table = 'avisos';

    protected $fillable = [
        'titulo', 'mensaje', 'tipo', 'fecha_inicio', 'fecha_fin', 'activo', 'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /** Avisos activos y vigentes para mostrarse como popup flotante hoy. */
    public function scopeActivosParaHoy($query)
    {
        $hoy = now()->toDateString();

        return $query->where('activo', true)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hoy);
            })
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
            })
            ->orderByRaw("FIELD(tipo, 'urgente','advertencia','info')")
            ->orderByDesc('created_at');
    }
}
