@extends('layouts.app')
@section('titulo', 'Clases dictadas')

@section('contenido')
<x-page-head titulo="Clases dictadas y canceladas"
             subtitulo="Todas las clases entre dos fechas y en qué quedó cada una."
             :volver="route('reportes.index')" volver-texto="Reportes">
    <form method="GET">
        <input type="date" name="desde" value="{{ $desde }}" class="form-control">
        <span class="text-muted">a</span>
        <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
        <button class="btn btn-light">Ver</button>
    </form>
    <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
</x-page-head>

<div class="stats">
    <x-stat label="Total de clases" :valor="$data->count()" />
    <x-stat label="Dictadas" :valor="$resumen['realizadas']" tono="verde" />
    <x-stat label="Por dictar" :valor="$resumen['programadas']" />
    <x-stat label="Canceladas" :valor="$resumen['canceladas']" tono="rojo" />
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Fecha</th><th>Alumno</th><th>Taller</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($data as $c)
                <tr>
                    <td class="text-nowrap">{{ $c->fecha->format('d/m/Y') }} <span class="text-muted">{{ \Carbon\Carbon::parse($c->hora_inicio)->format('H:i') }}</span></td>
                    <td class="fw-semibold">{{ $c->alumno->nombre ?? '—' }}</td>
                    <td>{{ $c->especialidad->nombre ?? '—' }} <span class="text-muted">· {{ $c->maestro->nombre ?? '—' }}</span></td>
                    <td>
                        <span class="badge {{ $c->estado === 'realizada' ? 'bg-success' : ($c->estado === 'cancelada' ? 'bg-danger' : 'bg-secondary') }}">{{ ['realizada' => 'Dictada', 'cancelada' => 'Cancelada', 'programada' => 'Por dictar'][$c->estado] ?? ucfirst($c->estado) }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="vacio"><i class="bi bi-calendar-x"></i>No hay clases en este rango de fechas.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
