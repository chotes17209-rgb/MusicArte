@extends('layouts.pdf')
@section('titulo', 'Alumnos por especialidad')
@section('subtitulo', $periodo ? 'Periodo '.$periodo->nombre : 'Sin periodo')

@section('contenido')
@php $totalTalleres = $data->sum('alumnos'); $max = max(1, $data->max('alumnos')); @endphp
<table class="resumen"><tr>
    <td><div class="etq">Alumnos distintos</div><div class="val">{{ $totalAlumnos }}</div><div class="det">en el periodo</div></td>
    <td><div class="etq">Inscripciones</div><div class="val">{{ $totalTalleres }}</div><div class="det">un alumno puede llevar varios talleres</div></td>
    <td><div class="etq">Especialidades</div><div class="val">{{ $data->count() }}</div><div class="det">con alumnos</div></td>
    <td><div class="etq">Más alumnos</div><div class="val" style="font-size:12px">{{ $data->first()['especialidad']->nombre ?? '—' }}</div><div class="det">{{ $data->first()['alumnos'] ?? 0 }} alumnos</div></td>
</tr></table>

<table class="tabla">
    <thead><tr><th>Especialidad</th><th>Maestros</th><th class="der">Alumnos</th><th style="width:30%">Proporción</th><th class="der">%</th></tr></thead>
    <tbody>
    @forelse($data as $fila)
        <tr>
            <td class="fuerte">{{ $fila['especialidad']->nombre ?? '—' }}</td>
            <td class="muted">{{ $fila['maestros']->implode(', ') ?: '—' }}</td>
            <td class="der fuerte">{{ $fila['alumnos'] }}</td>
            <td><div class="barra"><div style="width: {{ round($fila['alumnos'] / $max * 100) }}%; background: {{ $fila['especialidad']->color ?? '#3d2c8d' }}"></div></div></td>
            <td class="der">{{ $totalTalleres ? round($fila['alumnos'] / $totalTalleres * 100, 1) : 0 }}%</td>
        </tr>
    @empty
        <tr><td colspan="5" class="centro muted">No hay alumnos en este periodo.</td></tr>
    @endforelse
    </tbody>
    <tfoot><tr><td colspan="2">Total</td><td class="der">{{ $totalTalleres }}</td><td colspan="2"></td></tr></tfoot>
</table>
@endsection
