@extends('layouts.pdf')
@section('titulo', 'Planilla de maestros')
@section('subtitulo', \App\Models\Pago::MESES[(int) $mes].' '.$anio)

@section('contenido')
@php
    $porMaestro = $data->groupBy(fn ($p) => $p->maestro->nombre ?? 'Sin maestro')->sortKeys();
    $horas = fn ($h) => rtrim(rtrim(number_format($h, 1), '0'), '.');
@endphp
<table class="resumen"><tr>
    <td><div class="etq">Total a pagar</div><div class="val">S/ {{ number_format($data->sum('monto'), 2) }}</div></td>
    <td><div class="etq">Maestros</div><div class="val">{{ $porMaestro->count() }}</div></td>
    <td><div class="etq">Horas</div><div class="val">{{ $horas($data->sum('horas')) }}</div></td>
    <td><div class="etq">Alumnos</div><div class="val">{{ $data->pluck('alumno_id')->filter()->unique()->count() }}</div></td>
</tr></table>

<h2>Resumen por maestro</h2>
<table class="tabla">
    <thead><tr><th>Maestro</th><th class="der">Alumnos</th><th class="der">Horas</th><th class="der">Monto</th></tr></thead>
    <tbody>
    @foreach($porMaestro as $maestro => $filas)
        <tr><td class="fuerte">{{ $maestro }}</td><td class="der">{{ $filas->count() }}</td><td class="der">{{ $horas($filas->sum('horas')) }}</td><td class="der fuerte">S/ {{ number_format($filas->sum('monto'), 2) }}</td></tr>
    @endforeach
    </tbody>
    <tfoot><tr><td>Total</td><td class="der">{{ $data->count() }}</td><td class="der">{{ $horas($data->sum('horas')) }}</td><td class="der">S/ {{ number_format($data->sum('monto'), 2) }}</td></tr></tfoot>
</table>

@foreach($porMaestro as $maestro => $filas)
    <div class="no-cortar">
        <h2>{{ $maestro }} <span class="muted" style="font-weight:normal">· S/ {{ number_format($filas->sum('monto'), 2) }}</span></h2>
        <table class="tabla">
            <thead><tr><th>Alumno</th><th>Especialidad</th><th class="der">Horas</th><th class="der">Monto</th><th>Observación</th></tr></thead>
            <tbody>
            @foreach($filas as $p)
                <tr><td>{{ $p->alumno->nombre ?? '—' }}</td><td>{{ $p->especialidad->nombre ?? '—' }}</td><td class="der">{{ $p->horas }}</td><td class="der">S/ {{ number_format($p->monto, 2) }}</td><td class="muted">{{ $p->observacion }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endforeach
@if($data->isEmpty())<p class="muted">No hay planilla registrada para este mes.</p>@endif
@endsection
