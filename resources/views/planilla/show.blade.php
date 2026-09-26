@extends('layouts.app')
@section('titulo', 'Pago a maestro')

@section('contenido')
<x-page-head :titulo="($planilla->maestro->nombre ?? 'Maestro').' — '.\App\Models\Pago::MESES[$planilla->mes].' '.$planilla->anio"
             subtitulo="Detalle del pago de planilla." :volver="route('planilla.index')" volver-texto="Planilla" />

<div class="stats">
    <x-stat label="Monto" :valor="'S/ '.number_format($planilla->monto, 2)" />
    <x-stat label="Horas" :valor="$planilla->horas" />
</div>
<div class="card p-3">
    <dl class="ficha mb-0">
        <dt>Maestro</dt><dd>{{ $planilla->maestro->nombre ?? '—' }}</dd>
        <dt>Alumno</dt><dd>{{ $planilla->alumno->nombre ?? '—' }}</dd>
        <dt>Especialidad</dt><dd>{{ $planilla->especialidad->nombre ?? '—' }}</dd>
        <dt>Mes</dt><dd>{{ \App\Models\Pago::MESES[$planilla->mes] }} {{ $planilla->anio }}</dd>
        <dt>Observación</dt><dd>{{ $planilla->observacion ?: '—' }}</dd>
    </dl>
</div>
@endsection
