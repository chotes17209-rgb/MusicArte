@extends('layouts.app')
@section('titulo', 'Reporte: Clases')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Clases Dictadas / Canceladas</h5>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="GET">
            <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm">
            <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm">
            <button class="btn btn-sm btn-light">Filtrar</button>
        </form>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Programadas</div><div class="fs-4 fw-bold text-secondary">{{ $resumen['programadas'] }}</div></div></div>
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Realizadas</div><div class="fs-4 fw-bold text-success">{{ $resumen['realizadas'] }}</div></div></div>
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Canceladas</div><div class="fs-4 fw-bold text-danger">{{ $resumen['canceladas'] }}</div></div></div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Fecha</th><th>Hora</th><th>Alumno</th><th>Maestro</th><th>Especialidad</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($data as $c)
                <tr>
                    <td>{{ $c->fecha->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->hora_inicio)->format('H:i') }}</td>
                    <td class="fw-semibold">{{ $c->alumno->nombre ?? '—' }}</td>
                    <td>{{ $c->maestro->nombre ?? '—' }}</td>
                    <td>{{ $c->especialidad->nombre ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $c->estado === 'realizada' ? 'bg-success' : ($c->estado === 'cancelada' ? 'bg-danger' : 'bg-secondary') }}">{{ ucfirst($c->estado) }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hay clases registradas en este rango.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
