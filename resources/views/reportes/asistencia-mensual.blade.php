@extends('layouts.app')
@section('titulo', 'Reporte: Asistencia Mensual')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Asistencia Mensual</h5>
        <small class="text-muted">Reporte de asistencia por maestro (seccion 17)</small>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="GET">
            <select name="maestro_id" class="form-select form-select-sm" style="min-width:160px">
                <option value="">Todos los maestros</option>
                @foreach($maestros as $m)
                    <option value="{{ $m->id }}" @selected($maestroId==$m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
            <select name="mes" class="form-select form-select-sm">
                @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
            </select>
            <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm" style="width:90px">
            <button class="btn btn-sm btn-light">Filtrar</button>
        </form>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Total clases</th><th>Asistio</th><th>Faltas</th><th>Tardanzas</th><th>% Asistencia</th></tr></thead>
            <tbody>
            @forelse($data as $d)
                <tr>
                    <td class="fw-semibold">{{ $d['alumno'] }}</td>
                    <td>{{ $d['total'] }}</td>
                    <td class="text-success">{{ $d['asistio'] }}</td>
                    <td class="text-danger">{{ $d['faltas'] }}</td>
                    <td class="text-warning">{{ $d['tardanzas'] }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;max-width:100px">
                                <div class="progress-bar" style="width:{{ $d['porcentaje'] }}%;background:#3d2c8d"></div>
                            </div>
                            <span class="small">{{ $d['porcentaje'] }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Sin datos para el periodo seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
