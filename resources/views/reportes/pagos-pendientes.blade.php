@extends('layouts.app')
@section('titulo', 'Pagos pendientes')

@section('contenido')
<x-page-head titulo="Pagos pendientes de {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}"
             subtitulo="Alumnos que todavía deben algo del mes. Ordenado de mayor a menor deuda."
             :volver="route('reportes.index')" volver-texto="Reportes">
    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank" class="btn btn-light" data-sin-ventana><i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF</a>
</x-page-head>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET" data-autofiltro>
        <div class="col-6 col-md-2">
            <label class="form-label">Mes</label>
            <select name="mes" class="form-select">
                @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Año</label>
            <input type="number" name="anio" value="{{ $anio }}" class="form-control">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Taller</label>
            <select name="especialidad_id" class="form-select">
                <option value="">Todos</option>
                @foreach($especialidades as $e)
                    <option value="{{ $e->id }}" @selected(request('especialidad_id')==$e->id)>{{ $e->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Maestro</label>
            <select name="maestro_id" class="form-select">
                <option value="">Todos</option>
                @foreach($maestros as $m)
                    <option value="{{ $m->id }}" @selected(request('maestro_id')==$m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="">Sin pagar y a cuenta</option>
                <option value="pendiente" @selected(request('estado')=='pendiente')>Solo sin pagar</option>
                <option value="a_cuenta" @selected(request('estado')=='a_cuenta')>Solo a cuenta</option>
                <option value="pagado" @selected(request('estado')=='pagado')>Solo pagados</option>
            </select>
        </div>
    </form>
</div>

<div class="stats">
    <x-stat label="Total que se debe" :valor="'S/ '.number_format($data->sum('saldo'), 2)" tono="rojo" />
    <x-stat label="Pagos con saldo" :valor="$data->where('saldo', '>', 0)->count()" />
    <x-stat label="Alumnos" :valor="$data->pluck('alumno_id')->unique()->count()" />
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Taller</th><th class="text-end">Debe pagar</th><th class="text-end">Ya pagó</th><th class="text-end">Le falta</th><th>Estado</th></tr></thead>
            <tbody>
            @forelse($data as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                    <td>
                        <div>{{ $p->tallerLabel() }}</div>
                        <div class="small text-muted">{{ $p->alumnoTaller->maestro->nombre ?? '' }}</div>
                    </td>
                    <td class="text-end">S/ {{ number_format($p->monto_total, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($p->monto_total - $p->saldo, 2) }}</td>
                    <td class="text-end fw-semibold {{ $p->saldo > 0 ? 'tono-rojo' : 'tono-verde' }}">S/ {{ number_format($p->saldo, 2) }}</td>
                    <td>
                        <span class="badge {{ ['pendiente'=>'bg-danger','a_cuenta'=>'bg-warning','pagado'=>'bg-success'][$p->estado] ?? 'bg-secondary' }}">{{ $p->estadoLabel() }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="vacio"><i class="bi bi-check-circle"></i>No hay pagos pendientes con estos filtros.</div></td></tr>
            @endforelse
            </tbody>
            @if($data->isNotEmpty())
            <tfoot><tr><td colspan="4">Total</td><td class="text-end tono-rojo">S/ {{ number_format($data->sum('saldo'), 2) }}</td><td></td></tr></tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
