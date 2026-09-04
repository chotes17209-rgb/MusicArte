@extends('layouts.app')
@section('titulo', 'Historial de Pagos Anual')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('pagos.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Pagos</a>
        <h5 class="fw-semibold mb-0 mt-1">Historial de Pagos — {{ $anio }}</h5>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="GET">
            <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm" style="width:100px">
            <button class="btn btn-sm btn-light">Ver año</button>
        </form>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Mes</th><th>Pagos registrados</th><th>Facturado</th><th>Recaudado</th><th>Pendiente</th></tr></thead>
            <tbody>
            @foreach($resumen as $fila)
                <tr>
                    <td class="fw-semibold">{{ $fila['mes'] }}</td>
                    <td>{{ $fila['cantidad'] }}</td>
                    <td>S/ {{ number_format($fila['facturado'], 2) }}</td>
                    <td class="text-success">S/ {{ number_format($fila['recaudado'], 2) }}</td>
                    <td class="{{ $fila['pendiente'] > 0 ? 'text-danger fw-semibold' : '' }}">S/ {{ number_format($fila['pendiente'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td>Total {{ $anio }}</td>
                    <td>{{ $resumen->sum('cantidad') }}</td>
                    <td>S/ {{ number_format($resumen->sum('facturado'), 2) }}</td>
                    <td class="text-success">S/ {{ number_format($resumen->sum('recaudado'), 2) }}</td>
                    <td class="text-danger">S/ {{ number_format($resumen->sum('pendiente'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
