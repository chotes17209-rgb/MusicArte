<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    /** Estados en los que el alumno si recibio su clase. */
    public const PRESENTES = ['asistio', 'tardanza', 'recupero'];

    public const NOMBRES = [
        'asistio' => 'Asistió',
        'falto' => 'Faltó',
        'justificado' => 'Faltó con aviso',
        'tardanza' => 'Tardanza',
        'recupero' => 'Recuperó',
    ];

    public function estadoLabel(): string
    {
        return self::NOMBRES[$this->estado] ?? ucfirst((string) $this->estado);
    }

    protected $fillable = [
        'clase_id', 'alumno_id', 'estado', 'observacion', 'registrado_por',
    ];

    public function clase()
    {
        return $this->belongsTo(Clase::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
