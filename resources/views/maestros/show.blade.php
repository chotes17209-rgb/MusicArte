@extends('layouts.app')
@section('titulo', 'Perfil del maestro')

@section('contenido')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('maestros.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Maestros</a>
        <h4 class="fw-bold mb-0 mt-1" style="color:#3d2c8d">{{ $maestro->nombre }}</h4>
        <div class="d-flex gap-2 mt-1 flex-wrap">
            @if($maestro->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif
            @foreach($maestro->especialidades as $e)
                <span class="badge bg-light text-dark border">{{ $e->nombre }}</span>
            @endforeach
        </div>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="periodo_id" class="form-select form-select-sm" style="min-width:180px" onchange="this.form.submit()">
            @foreach($periodos as $p)
                <option value="{{ $p->id }}" @selected($periodo?->id == $p->id)>{{ $p->nombre }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-person-badge me-1"></i> Datos de contacto</h6>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">Teléfono</dt><dd class="col-7">{{ $maestro->telefono ?? '—' }}</dd>
                <dt class="col-5 text-muted">Email</dt><dd class="col-7">{{ $maestro->email ?? '—' }}</dd>
            </dl>
            <hr>
            <div class="small text-muted">
                Alumnos en el periodo {{ $periodo?->nombre ?? '—' }}: <span class="fw-semibold">{{ $horarios->pluck('alumno_id')->unique()->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if(!$periodo)
            <div class="alert alert-warning">No hay periodos registrados todavía.</div>
        @else
            @include('horarios._grid-maestro', ['maestro' => $maestro, 'horarios' => $horarios])
        @endif
    </div>
</div>
@endsection
