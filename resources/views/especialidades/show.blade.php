@extends('layouts.app')
@section('titulo', 'Especialidad')

@section('contenido')
<x-page-head :titulo="$especialidad->nombre" :subtitulo="'Alumnos y maestros de '.$especialidad->nombre.($periodo ? ' en '.$periodo->nombre : '').'.'"
             :volver="route('especialidades.index')" volver-texto="Especialidades" />

<div class="stats">
    <x-stat label="Alumnos en el periodo" :valor="$talleres->pluck('alumno_id')->unique()->count()" :detalle="$periodo->nombre ?? null" />
    <x-stat label="Maestros que la dictan" :valor="$especialidad->maestros->count()" />
    @if(auth()->user()->esAdmin())
        <x-stat label="Precio mensual" :valor="'S/ '.number_format($especialidad->precio_mensual, 2)" />
    @endif
    <x-stat label="Estado" :valor="$especialidad->activo ? 'Activa' : 'Inactiva'" :tono="$especialidad->activo ? 'verde' : null" />
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <h6 class="seccion-titulo">Alumnos {{ $periodo ? 'de '.$periodo->nombre : '' }}</h6>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Alumno</th><th>Maestro</th></tr></thead>
                    <tbody>
                    @forelse($talleres as $t)
                        <tr>
                            <td class="fw-semibold"><a href="{{ route('alumnos.show', $t->alumno_id) }}" class="text-body">{{ $t->alumno->nombre ?? '—' }}</a></td>
                            <td>{{ $t->maestro->nombre ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2"><div class="vacio">No hay alumnos en este periodo.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5 d-flex flex-column gap-3">
        <div class="card p-3">
            <h6 class="seccion-titulo">Maestros</h6>
            @forelse($especialidad->maestros as $m)
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <a href="{{ route('maestros.show', $m) }}" class="text-body">{{ $m->nombre }}</a>
                    <span class="text-muted small">S/ {{ number_format($m->pivot->tarifa_hora, 2) }} por hora</span>
                </div>
            @empty
                <div class="text-muted small">Ningún maestro asignado.</div>
            @endforelse
        </div>
        <div class="card p-3">
            <h6 class="seccion-titulo">Alumnos por mes</h6>
            @php $max = max(1, $historial->max('alumnos')); @endphp
            @foreach($historial as $h)
                <div class="d-flex align-items-center gap-2 py-1">
                    <span class="small text-muted" style="width:110px">{{ $h['periodo'] }}</span>
                    <div class="barra"><span style="width: {{ $h['alumnos'] / $max * 100 }}%; background: {{ $especialidad->color }}"></span></div>
                    <span class="small fw-semibold" style="width:28px;text-align:right">{{ $h['alumnos'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
