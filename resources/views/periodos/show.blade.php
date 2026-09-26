@extends('layouts.app')
@section('titulo', 'Periodo')

@section('contenido')
<x-page-head :titulo="$periodo->nombre"
             :subtitulo="$periodo->fecha_inicio->format('d/m/Y').' al '.$periodo->fecha_fin->format('d/m/Y').' · '.$periodo->duracionSemanas().' semanas'.($periodo->estaEnCurso() ? ' · en curso' : '')"
             :volver="route('alumnos.index')" volver-texto="Alumnos" />

<div class="stats">
    <x-stat label="Alumnos inscritos" :valor="$resumen['alumnos']" :detalle="$resumen['talleres'].' talleres'" />
    <x-stat label="Clases" :valor="$resumen['clases']" :detalle="$resumen['dictadas'].' dictadas'" />
    <x-stat label="Asistencia" :valor="$resumen['asistencia'] !== null ? $resumen['asistencia'].'%' : '—'"
            :tono="$resumen['asistencia'] === null ? null : ($resumen['asistencia'] >= 85 ? 'verde' : ($resumen['asistencia'] >= 65 ? 'ambar' : 'rojo'))" />
    <x-stat label="Pagos al día" :valor="$resumen['pagos_pagados'].' de '.$resumen['pagos_total']" />
    <x-stat label="Estado" :valor="$periodo->activo ? 'Abierto' : 'Cerrado'" :detalle="$periodo->activo ? 'Se puede inscribir alumnos' : 'Solo historial'" />
</div>

<div class="card p-3">
    <h6 class="seccion-titulo">Alumnos por especialidad</h6>
    @php $max = max(1, $porEspecialidad->max('alumnos')); @endphp
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Especialidad</th><th>Maestros</th><th class="text-end">Alumnos</th><th style="min-width:160px"></th></tr></thead>
            <tbody>
            @forelse($porEspecialidad as $nombre => $e)
                <tr>
                    <td class="fw-semibold"><span class="chip-punto d-inline-block me-2" style="background: {{ $e['color'] }}"></span>{{ $nombre }}</td>
                    <td class="text-muted">{{ $e['maestros'] }}</td>
                    <td class="text-end fw-semibold">{{ $e['alumnos'] }}</td>
                    <td><div class="barra"><span style="width: {{ $e['alumnos'] / $max * 100 }}%; background: {{ $e['color'] }}"></span></div></td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="vacio">Aún no hay alumnos inscritos en este periodo.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
