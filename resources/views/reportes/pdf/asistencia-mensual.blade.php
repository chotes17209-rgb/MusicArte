@extends('layouts.pdf')
@section('titulo', 'Asistencia mensual')
@section('subtitulo', \App\Models\Pago::MESES[$mes].' '.$anio.($maestroId ? ' · Maestro: '.($maestros->firstWhere('id', (int) $maestroId)->nombre ?? '') : ' · Todos los maestros'))

@section('contenido')
@php $tono = fn ($p) => $p === null ? 'muted' : ($p >= 85 ? 'verde' : ($p >= 65 ? 'ambar' : 'rojo')); @endphp
<table class="resumen"><tr>
    <td><div class="etq">Asistencia</div><div class="val {{ $tono($resumen['porcentaje']) }}">{{ $resumen['porcentaje'] !== null ? $resumen['porcentaje'].'%' : '—' }}</div><div class="det">sobre clases marcadas</div></td>
    <td><div class="etq">Clases</div><div class="val">{{ $resumen['clases'] }}</div><div class="det">{{ $data->count() }} alumnos</div></td>
    <td><div class="etq">Asistencias</div><div class="val verde">{{ $resumen['asistio'] }}</div><div class="det">incluye tardanzas</div></td>
    <td><div class="etq">Faltas</div><div class="val rojo">{{ $resumen['faltas'] }}</div><div class="det">{{ $resumen['sin_marcar'] }} clases sin marcar</div></td>
</tr></table>

<table class="tabla">
    <thead><tr><th>Alumno</th><th>Taller · maestro</th><th class="der">Clases</th><th class="der">Asistió</th><th class="der">Con aviso</th><th class="der">Faltó</th><th class="der">Sin marcar</th><th style="width:15%">Asistencia</th><th class="der">%</th></tr></thead>
    <tbody>
    @forelse($data as $fila)
        <tr class="no-cortar">
            <td class="fuerte">{{ $fila['alumno']->nombre ?? '—' }}</td>
            <td class="muted">{!! $fila['talleres']->map(fn ($t) => e($t))->implode('<br>') !!}</td>
            <td class="der">{{ $fila['total'] }}</td>
            <td class="der verde">{{ $fila['asistio'] }}</td>
            <td class="der ambar">{{ $fila['justificado'] ?: '' }}</td>
            <td class="der rojo">{{ $fila['faltas'] ?: '' }}</td>
            <td class="der muted">{{ $fila['sin_marcar'] ?: '' }}</td>
            <td>@if($fila['porcentaje'] !== null)<div class="barra"><div style="width: {{ $fila['porcentaje'] }}%; background: {{ $fila['porcentaje'] >= 85 ? '#1d7a46' : ($fila['porcentaje'] >= 65 ? '#c28a00' : '#b42318') }}"></div></div>@endif</td>
            <td class="der fuerte {{ $tono($fila['porcentaje']) }}">{{ $fila['porcentaje'] !== null ? $fila['porcentaje'].'%' : '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="9" class="centro muted">No hay clases en este mes.</td></tr>
    @endforelse
    </tbody>
    <tfoot><tr><td colspan="2">Total</td><td class="der">{{ $resumen['clases'] }}</td><td class="der">{{ $resumen['asistio'] }}</td><td class="der">{{ $data->sum('justificado') }}</td><td class="der">{{ $data->sum('faltas') }}</td><td class="der">{{ $resumen['sin_marcar'] }}</td><td></td><td class="der">{{ $resumen['porcentaje'] !== null ? $resumen['porcentaje'].'%' : '—' }}</td></tr></tfoot>
</table>
@endsection
