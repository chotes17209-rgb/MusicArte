@extends('layouts.app')
@section('titulo', 'Reporte: Pagos Pendientes')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('reportes.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Reportes</a>
        <h5 class="fw-semibold mb-0 mt-1">Alumnos con pagos pendientes o parciales</h5>
        <small class="text-muted">Seccion 14: filtra por periodo, maestro, taller y estado de pago</small>
    </div>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-2">
            <select name="mes" class="form-select form-select-sm">
                @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <select name="especialidad_id" class="form-select form-select-sm">
                <option value="">Todos los talleres</option>
                @foreach($especialidades as $e)
                    <option value="{{ $e->id }}" @selected(request('especialidad_id')==$e->id)>{{ $e->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="maestro_id" class="form-select form-select-sm">
                <option value="">Todos los maestros</option>
                @foreach($maestros as $m)
                    <option value="{{ $m->id }}" @selected(request('maestro_id')==$m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select form-select-sm">
                <option value="">Pendientes y a cuenta</option>
                <option value="pendiente" @selected(request('estado')=='pendiente')>Solo pendientes</option>
                <option value="a_cuenta" @selected(request('estado')=='a_cuenta')>Solo a cuenta</option>
                <option value="pagado" @selected(request('estado')=='pagado')>Solo pagados</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-sm btn-morado">Filtrar</button>
        </div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Taller</th><th>Maestro</th><th>Monto</th><th>Pagado</th><th>Saldo</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($data as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                    <td>{{ $p->alumnoTaller->especialidad->nombre ?? '—' }}</td>
                    <td>{{ $p->alumnoTaller->maestro->nombre ?? '—' }}</td>
                    <td>S/ {{ number_format($p->monto_total, 2) }}</td>
                    <td>S/ {{ number_format($p->monto_total - $p->saldo, 2) }}</td>
                    <td class="fw-semibold {{ $p->saldo > 0 ? 'text-danger' : 'text-success' }}">S/ {{ number_format($p->saldo, 2) }}</td>
                    <td>
                        <span class="badge {{ ['pendiente'=>'bg-danger','a_cuenta'=>'bg-warning text-dark','pagado'=>'bg-success'][$p->estado] ?? 'bg-secondary' }}">
                            {{ $p->estadoLabel() }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hay pagos que coincidan con los filtros.</td></tr>
            @endforelse
            </tbody>
            @if($data->count())
                <tfoot>
                    <tr class="fw-semibold">
                        <td colspan="5" class="text-end">Total pendiente:</td>
                        <td class="text-danger">S/ {{ number_format($data->sum('saldo'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
