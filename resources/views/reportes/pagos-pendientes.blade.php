@extends('layouts.app')
@section('titulo', 'Reporte: Pagos Pendientes')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Pagos Pendientes — {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}</h5>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="GET">
            <select name="mes" class="form-select form-select-sm">
                @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
            </select>
            <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm" style="width:90px">
            <button class="btn btn-sm btn-light">Filtrar</button>
        </form>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
</div>

@if($data->isEmpty())
<div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i> No hay pagos pendientes para este periodo.</div>
@endif

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Concepto</th><th>Monto total</th><th>Saldo pendiente</th></tr></thead>
            <tbody>
            @forelse($data as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                    <td>{{ $p->concepto ?? '—' }}</td>
                    <td>S/ {{ number_format($p->monto_total, 2) }}</td>
                    <td class="text-danger fw-bold">S/ {{ number_format($p->saldo, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Sin datos.</td></tr>
            @endforelse
            </tbody>
            <tfoot><tr class="fw-bold"><td colspan="3">Total pendiente</td><td class="text-danger">S/ {{ number_format($data->sum('saldo'), 2) }}</td></tr></tfoot>
        </table>
    </div>
</div>
@endsection
