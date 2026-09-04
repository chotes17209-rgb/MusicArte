@extends('layouts.app')
@section('titulo', 'Reporte: Ingresos vs Egresos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Ingresos vs Egresos {{ $anio }}</h5>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="GET">
            <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm" style="width:90px">
            <button class="btn btn-sm btn-light">Filtrar</button>
        </form>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Mes</th><th>Ingresos</th><th>Egresos</th><th>Balance</th></tr></thead>
            <tbody>
            @foreach($data as $d)
                <tr>
                    <td class="fw-semibold">{{ $d['mes'] }}</td>
                    <td class="text-success">S/ {{ number_format($d['ingresos'], 2) }}</td>
                    <td class="text-danger">S/ {{ number_format($d['egresos'], 2) }}</td>
                    <td class="fw-bold {{ $d['balance'] >= 0 ? 'text-success' : 'text-danger' }}">S/ {{ number_format($d['balance'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td>Total</td>
                    <td class="text-success">S/ {{ number_format($data->sum('ingresos'), 2) }}</td>
                    <td class="text-danger">S/ {{ number_format($data->sum('egresos'), 2) }}</td>
                    <td>S/ {{ number_format($data->sum('balance'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
