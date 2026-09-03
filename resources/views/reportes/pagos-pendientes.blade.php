@extends('layouts.app')
@section('titulo', 'Reporte: Estado de Pagos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Estado de Pagos — {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}</h5>
    </div>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-2">
            <select name="mes" class="form-select">
                @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="anio" value="{{ $anio }}" class="form-control">
        </div>
        <div class="col-md-3">
            <select name="maestro_id" class="form-select">
                <option value="">Todos los maestros</option>
                @foreach($maestros as $m)<option value="{{ $m->id }}" @selected(request('maestro_id')==$m->id)>{{ $m->nombre }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="especialidad_id" class="form-select">
                <option value="">Todos los talleres</option>
                @foreach($especialidades as $esp)<option value="{{ $esp->id }}" @selected(request('especialidad_id')==$esp->id)>{{ $esp->nombre }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">Pendientes y a cuenta</option>
                @foreach(\App\Models\Pago::ESTADOS as $key => $label)<option value="{{ $key }}" @selected(request('estado')==$key)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-12"><button class="btn btn-light">Filtrar</button></div>
    </form>
</div>

@if($data->isEmpty())
<div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i> No hay pagos pendientes con estos filtros.</div>
@endif

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Taller</th><th>Maestro</th><th>Monto total</th><th>Pagado</th><th>Saldo pendiente</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($data as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                    <td>{{ $p->especialidad->nombre ?? '—' }}</td>
                    <td>{{ $p->maestro->nombre ?? '—' }}</td>
                    <td>S/ {{ number_format($p->monto_total, 2) }}</td>
                    <td>S/ {{ number_format($p->monto_pagado, 2) }}</td>
                    <td class="text-danger fw-bold">S/ {{ number_format($p->saldo, 2) }}</td>
                    <td>
                        @if($p->estado === 'a_cuenta')<span class="badge bg-warning text-dark">A cuenta</span>
                        @else<span class="badge bg-danger">Pendiente</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Sin datos.</td></tr>
            @endforelse
            </tbody>
            <tfoot><tr class="fw-bold"><td colspan="5">Total pendiente</td><td class="text-danger">S/ {{ number_format($data->sum('saldo'), 2) }}</td><td></td></tr></tfoot>
        </table>
    </div>
</div>
@endsection
