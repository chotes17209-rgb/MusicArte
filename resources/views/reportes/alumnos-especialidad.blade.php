@extends('layouts.app')
@section('titulo', 'Alumnos por especialidad')

@section('contenido')
<x-page-head titulo="Alumnos por especialidad"
             subtitulo="Cuántos alumnos hay en cada taller durante el periodo elegido. Un alumno con dos talleres cuenta en ambos."
             :volver="route('reportes.index')" volver-texto="Reportes">
    <form method="GET" data-autofiltro>
        <select name="periodo_id" class="form-select">
            @foreach($periodos as $p)
                <option value="{{ $p->id }}" @selected($periodo && $periodo->id == $p->id)>{{ $p->nombre }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank" class="btn btn-light" data-sin-ventana><i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF</a>
</x-page-head>

<div class="stats">
    <x-stat label="Alumnos en el periodo" :valor="$totalAlumnos" :detalle="$periodo->nombre ?? ''" />
    <x-stat label="Especialidades con alumnos" :valor="$data->count()" />
    <x-stat label="Taller con más alumnos" :valor="$data->first()['especialidad']->nombre ?? '—'" :detalle="isset($data[0]) ? $data[0]['alumnos'].' alumnos' : null" />
</div>

@php $max = max(1, $data->max('alumnos')); @endphp
<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Especialidad</th><th>Maestros</th><th class="text-end">Alumnos</th><th style="min-width:200px">Proporción</th></tr></thead>
            <tbody>
            @forelse($data as $e)
                <tr>
                    <td class="fw-semibold">
                        <span class="d-inline-block rounded-circle me-2" style="width:8px;height:8px;background:{{ $e['especialidad']->color ?? '#999' }}"></span>{{ $e['especialidad']->nombre ?? '—' }}
                    </td>
                    <td class="text-muted">{{ $e['maestros']->implode(', ') }}</td>
                    <td class="text-end fw-semibold">{{ $e['alumnos'] }}</td>
                    <td><x-barra :porcentaje="$e['alumnos'] / $max * 100" tono="acento" /></td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="vacio"><i class="bi bi-people"></i>No hay alumnos inscritos en este periodo.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
