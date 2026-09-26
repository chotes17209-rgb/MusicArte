@extends('layouts.pdf')
@section('titulo', 'Clases')
@section('subtitulo', 'Del '.\Carbon\Carbon::parse($desde)->format('d/m/Y').' al '.\Carbon\Carbon::parse($hasta)->format('d/m/Y'))

@section('contenido')
@php
    $estados = ['programada' => 'Por dictar', 'realizada' => 'Dictada', 'cancelada' => 'Cancelada'];
    $porMaestro = $data->groupBy(fn ($c) => $c->maestro->nombre ?? 'Sin maestro')->sortKeys();
@endphp
<table class="resumen"><tr>
    <td><div class="etq">Clases</div><div class="val">{{ $data->count() }}</div><div class="det">{{ $data->pluck('alumno_id')->unique()->count() }} alumnos</div></td>
    <td><div class="etq">Dictadas</div><div class="val verde">{{ $resumen['realizadas'] }}</div></td>
    <td><div class="etq">Por dictar</div><div class="val">{{ $resumen['programadas'] }}</div></td>
    <td><div class="etq">Canceladas</div><div class="val rojo">{{ $resumen['canceladas'] }}</div></td>
</tr></table>

<h2>Por maestro</h2>
<table class="tabla">
    <thead><tr><th>Maestro</th><th class="der">Clases</th><th class="der">Dictadas</th><th class="der">Por dictar</th><th class="der">Canceladas</th></tr></thead>
    <tbody>
    @foreach($porMaestro as $maestro => $cs)
        <tr><td class="fuerte">{{ $maestro }}</td><td class="der">{{ $cs->count() }}</td><td class="der verde">{{ $cs->where('estado', 'realizada')->count() }}</td><td class="der">{{ $cs->where('estado', 'programada')->count() }}</td><td class="der rojo">{{ $cs->where('estado', 'cancelada')->count() ?: '' }}</td></tr>
    @endforeach
    </tbody>
</table>

<h2>Detalle</h2>
<table class="tabla">
    <thead><tr><th>Fecha</th><th>Hora</th><th>Alumno</th><th>Taller</th><th>Maestro</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse($data as $c)
        <tr>
            <td>{{ $c->fecha->translatedFormat('D d/m') }}</td>
            <td>{{ \Carbon\Carbon::parse($c->hora_inicio)->format('g:i a') }}</td>
            <td class="fuerte">{{ $c->alumno->nombre ?? '—' }}</td>
            <td>{{ $c->especialidad->nombre ?? '—' }}</td>
            <td class="muted">{{ $c->maestro->nombre ?? '—' }}</td>
            <td><span class="estado {{ $c->estado }}">{{ $estados[$c->estado] ?? $c->estado }}</span></td>
        </tr>
    @empty
        <tr><td colspan="6" class="centro muted">No hay clases en este rango.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
