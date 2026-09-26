@extends('layouts.app')
@section('titulo', 'Aviso')

@section('contenido')
@php $tipos = ['urgente' => ['Urgente', 'bg-danger'], 'advertencia' => ['Advertencia', 'bg-warning'], 'info' => ['Informativo', 'bg-info']]; @endphp
<x-page-head :titulo="$aviso->titulo" :volver="route('avisos.index')" volver-texto="Avisos" />

<div class="card p-3 mb-3">
    <div class="mb-3"><span class="badge {{ $tipos[$aviso->tipo][1] ?? 'bg-secondary' }}">{{ $tipos[$aviso->tipo][0] ?? ucfirst($aviso->tipo) }}</span></div>
    <p class="mb-0" style="white-space: pre-line">{{ $aviso->mensaje }}</p>
</div>
<div class="card p-3">
    <dl class="ficha mb-0">
        <dt>Se muestra</dt><dd>{{ $aviso->fecha_inicio ? \Carbon\Carbon::parse($aviso->fecha_inicio)->format('d/m/Y') : 'Desde hoy' }} — {{ $aviso->fecha_fin ? \Carbon\Carbon::parse($aviso->fecha_fin)->format('d/m/Y') : 'sin fecha de fin' }}</dd>
        <dt>Estado</dt><dd>@if($aviso->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</dd>
        <dt>Publicado por</dt><dd>{{ $aviso->autor->name ?? '—' }}</dd>
        <dt>Creado</dt><dd>{{ optional($aviso->created_at)->format('d/m/Y H:i') ?? '—' }}</dd>
    </dl>
</div>
@endsection
