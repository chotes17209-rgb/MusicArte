@extends('layouts.app')
@section('titulo', 'Reporte: Alumnos por Especialidad')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Alumnos por Especialidad</h5>
    </div>
    <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Especialidad</th><th>Alumnos activos</th></tr></thead>
            <tbody>
            @forelse($data as $e)
                <tr><td class="fw-semibold">{{ $e->nombre }}</td><td>{{ $e->alumnos_count }}</td></tr>
            @empty
                <tr><td colspan="2" class="text-center text-muted py-4">Sin datos.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold"><td>Total</td><td>{{ $data->sum('alumnos_count') }}</td></tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
