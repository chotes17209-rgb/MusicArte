@extends('layouts.app')
@section('titulo', 'Planilla de maestros')

@section('contenido')
<x-page-head titulo="Planilla de maestros — {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}"
             subtitulo="Lo pagado a cada maestro en el mes, con el detalle por alumno."
             :volver="route('reportes.index')" volver-texto="Reportes">
    <form method="GET">
        <select name="mes" class="form-select" onchange="this.form.submit()">
            @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
        </select>
        <input type="number" name="anio" value="{{ $anio }}" class="form-control" style="width:96px" onchange="this.form.submit()">
    </form>
    <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
</x-page-head>

@php $porMaestro = $data->groupBy(fn ($p) => $p->maestro->nombre ?? 'Sin maestro')->sortKeys(); @endphp
<div class="stats">
    <x-stat label="Total pagado" :valor="'S/ '.number_format($data->sum('monto'), 2)" />
    <x-stat label="Maestros" :valor="$porMaestro->count()" />
    <x-stat label="Horas" :valor="rtrim(rtrim(number_format($data->sum('horas'), 1), '0'), '.')" />
</div>

@forelse($porMaestro as $maestro => $filas)
    <div class="card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
            <h6 class="seccion-titulo mb-0">{{ $maestro }}</h6>
            <span class="fw-semibold">S/ {{ number_format($filas->sum('monto'), 2) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Alumno</th><th>Especialidad</th><th class="text-end">Horas</th><th class="text-end">Monto</th></tr></thead>
                <tbody>
                @foreach($filas as $p)
                    <tr>
                        <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                        <td>{{ $p->especialidad->nombre ?? '—' }}</td>
                        <td class="text-end">{{ $p->horas }}</td>
                        <td class="text-end">S/ {{ number_format($p->monto, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card"><div class="vacio"><i class="bi bi-file-earmark-text"></i>No hay planilla registrada para {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}.</div></div>
@endforelse
@endsection
