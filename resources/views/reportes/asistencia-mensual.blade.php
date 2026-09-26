@extends('layouts.app')
@section('titulo', 'Reporte de asistencia')

@section('contenido')
<x-page-head titulo="Asistencia de {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}"
             subtitulo="Cuántas clases tuvo cada alumno en el mes y a cuántas asistió. El porcentaje se calcula sobre las clases ya marcadas."
             :volver="route('reportes.index')" volver-texto="Reportes">
    <form method="GET">
        <select name="maestro_id" class="form-select" onchange="this.form.submit()">
            <option value="">Todos los maestros</option>
            @foreach($maestros as $m)
                <option value="{{ $m->id }}" @selected($maestroId==$m->id)>{{ $m->nombre }}</option>
            @endforeach
        </select>
        <select name="mes" class="form-select" onchange="this.form.submit()">
            @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
        </select>
        <input type="number" name="anio" value="{{ $anio }}" class="form-control" style="width:90px" onchange="this.form.submit()">
    </form>
    <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
</x-page-head>

<div class="stats">
    <x-stat label="Asistencia promedio" :valor="$resumen['porcentaje'] !== null ? $resumen['porcentaje'].'%' : '—'"
            :tono="$resumen['porcentaje'] === null ? null : ($resumen['porcentaje'] >= 85 ? 'verde' : ($resumen['porcentaje'] >= 65 ? 'ambar' : 'rojo'))" />
    <x-stat label="Clases del mes" :valor="$resumen['clases']" :detalle="$data->count().' alumnos'" />
    <x-stat label="Asistió" :valor="$resumen['asistio']" tono="verde" />
    <x-stat label="Faltó" :valor="$resumen['faltas']" tono="rojo" />
    <x-stat label="Sin marcar" :valor="$resumen['sin_marcar']" detalle="Clases sin asistencia registrada" />
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Alumno</th><th>Taller</th>
                    <th class="text-end">Clases</th><th class="text-end">Asistió</th>
                    <th class="text-end">Faltó con aviso</th><th class="text-end">Faltó sin aviso</th>
                    <th class="text-end">Sin marcar</th><th style="min-width:170px">Asistencia</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $d)
                <tr>
                    <td class="fw-semibold">{{ $d['alumno']->nombre ?? '—' }}</td>
                    <td>@foreach($d['talleres'] as $t)<div class="small">{{ $t }}</div>@endforeach</td>
                    <td class="text-end">{{ $d['total'] }}</td>
                    <td class="text-end tono-verde">{{ $d['asistio'] }}</td>
                    <td class="text-end">{{ $d['justificado'] ?: '—' }}</td>
                    <td class="text-end {{ $d['faltas'] ? 'tono-rojo' : '' }}">{{ $d['faltas'] ?: '—' }}</td>
                    <td class="text-end text-muted">{{ $d['sin_marcar'] ?: '—' }}</td>
                    <td>
                        @if($d['porcentaje'] !== null)
                            <x-barra :porcentaje="$d['porcentaje']" />
                        @else
                            <span class="text-muted small">Sin marcar aún</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="vacio"><i class="bi bi-calendar-x"></i>No hay clases en {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
