@extends('layouts.app')
@section('titulo', 'Tablero de Horarios')

@section('contenido')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('horarios.index', ['periodo_id' => $periodo?->id]) }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Horarios</a>
        <h5 class="fw-semibold mb-0 mt-1">Tablero de Horarios por Maestro</h5>
        <small class="text-muted">Igual que el cuadro físico de salón — cambia el periodo para ver cómo variaron maestros y horarios de un mes a otro</small>
    </div>
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <select name="periodo_id" class="form-select form-select-sm" style="min-width:180px" onchange="this.form.submit()">
            @foreach($periodos as $p)
                <option value="{{ $p->id }}" @selected($periodo?->id == $p->id)>{{ $p->nombre }}</option>
            @endforeach
        </select>
        <select name="maestro_id" class="form-select form-select-sm" style="min-width:180px" onchange="this.form.submit()">
            <option value="">Todos los maestros</option>
            @foreach(\App\Models\Maestro::where('activo', true)->orderBy('nombre')->get() as $m)
                <option value="{{ $m->id }}" @selected($maestroFiltroId == $m->id)>{{ $m->nombre }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()" type="button"><i class="bi bi-printer"></i></button>
    </form>
</div>

@if(!$periodo)
    <div class="alert alert-warning">No hay periodos registrados todavía. Crea uno primero en el módulo de Periodos.</div>
@elseif($maestros->isEmpty())
    <div class="alert alert-info">No hay maestros activos registrados.</div>
@else
    <div class="row g-3">
        @foreach($maestros as $maestro)
            @php $horarios = $horariosPorMaestro->get($maestro->id, collect()); @endphp
            <div class="col-12 {{ $maestros->count() > 1 ? 'col-xl-6' : '' }}">
                @include('horarios._grid-maestro', ['maestro' => $maestro, 'horarios' => $horarios])
                <div class="text-end mb-2">
                    <a href="{{ route('maestros.show', ['maestro' => $maestro, 'periodo_id' => $periodo->id]) }}" class="small">Ver perfil de {{ $maestro->nombre }} &rarr;</a>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('estilos')
<style>
    @media print {
        .sidebar, .topbar, form, .btn, a.small { display: none !important; }
        .content-wrap { margin-left: 0 !important; }
        .tablero-maestro { break-inside: avoid; }
    }
</style>
@endpush
