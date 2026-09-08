@extends('layouts.app')
@section('titulo', 'Reporte: Historial de Pagos Anual')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Historial de Pagos — Año {{ $anio }}</h5>
        <small class="text-muted">Seccion 13: consulta de enero a diciembre — pagados, a cuenta y pendientes</small>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="GET">
            <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm" style="width:100px">
            <button class="btn btn-sm btn-light">Ver año</button>
        </form>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Total facturado</div><div class="fs-4 fw-bold" style="color:#3d2c8d">S/ {{ number_format($totales['facturado'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Total cobrado</div><div class="fs-4 fw-bold text-success">S/ {{ number_format($totales['cobrado'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Total pendiente</div><div class="fs-4 fw-bold text-danger">S/ {{ number_format($totales['pendiente'], 2) }}</div></div></div>
</div>

<div class="card p-3 mb-3">
    <h6 class="fw-semibold mb-3">Detalle por mes</h6>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Mes</th><th>Facturado</th><th>Cobrado</th><th>Pendiente</th>
                    <th>Pagados</th><th>A cuenta</th><th>Pendientes</th>
                </tr>
            </thead>
            <tbody>
            @foreach($porMes as $m)
                <tr>
                    <td class="fw-semibold">{{ $m['label'] }}</td>
                    <td>S/ {{ number_format($m['facturado'], 2) }}</td>
                    <td class="text-success">S/ {{ number_format($m['cobrado'], 2) }}</td>
                    <td class="text-danger">S/ {{ number_format($m['pendiente'], 2) }}</td>
                    <td><span class="badge bg-success">{{ $m['cant_pagado'] }}</span></td>
                    <td><span class="badge bg-warning text-dark">{{ $m['cant_a_cuenta'] }}</span></td>
                    <td><span class="badge bg-danger">{{ $m['cant_pendiente'] }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card p-3">
    <h6 class="fw-semibold mb-3">Alumnos que deben (año {{ $anio }})</h6>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Meses pendientes</th><th>Total que debe</th></tr></thead>
            <tbody>
            @forelse($deudores as $d)
                <tr>
                    <td class="fw-semibold">{{ $d['alumno'] ?? '—' }}</td>
                    <td>{{ $d['meses_pendientes'] }}</td>
                    <td class="text-danger fw-semibold">S/ {{ number_format($d['total_debe'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">No hay deudores registrados este año. 🎉</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
