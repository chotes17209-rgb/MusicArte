@extends('layouts.app')
@section('titulo', 'Horarios')

@section('contenido')
@include('horarios._tabs')

<x-page-head :titulo="'Horarios de '.($periodo->nombre ?? '—')"
             subtitulo="El cuadro de cada maestro, como la hoja que se pega en el salón. Toca un alumno para ver su perfil.">
    <form method="GET" data-autofiltro>
        <select name="periodo_id" class="form-select" aria-label="Periodo">
            @foreach($periodos as $p)
                <option value="{{ $p->id }}" @selected($periodo?->id == $p->id)>{{ $p->nombre }}</option>
            @endforeach
        </select>
        <select name="maestro_id" class="form-select" aria-label="Maestro">
            <option value="">Todos los maestros</option>
            @foreach($todosMaestros as $m)
                <option value="{{ $m->id }}" @selected($maestroFiltroId == $m->id)>{{ $m->nombre }}</option>
            @endforeach
        </select>
    </form>
    @if($periodo)
        <a href="{{ route('horarios.tablero.pdf', ['periodo_id' => $periodo->id, 'maestro_id' => $maestroFiltroId]) }}" class="btn btn-light" target="_blank" data-sin-ventana><i class="bi bi-file-earmark-pdf me-1"></i> PDF para imprimir</a>
    @endif
</x-page-head>

@if(!$periodo)
    <div class="alert alert-warning">No hay periodos registrados todavía. Crea uno primero en Alumnos → Periodos.</div>
@elseif($maestros->isEmpty())
    <div class="card"><div class="vacio"><i class="bi bi-calendar-x"></i>No hay horarios en {{ $periodo->nombre }}.</div></div>
@else
    <div class="cuadros">
        @foreach($maestros as $maestro)
            @include('horarios._grid-maestro', ['maestro' => $maestro, 'horarios' => $horariosPorMaestro->get($maestro->id, collect())])
        @endforeach
    </div>
    @if($sinHorario->isNotEmpty())
        <p class="small text-muted mt-3 mb-0">Sin clases en {{ $periodo->nombre }}: {{ $sinHorario->pluck('nombre')->implode(', ') }}.</p>
    @endif
@endif
@endsection
