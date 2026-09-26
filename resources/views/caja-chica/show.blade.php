@extends('layouts.app')
@section('titulo', 'Movimiento de caja chica')

@section('contenido')
<x-page-head :titulo="$movimiento->descripcion" :subtitulo="'Caja chica · '.\Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y')" :volver="route('caja-chica.index')" volver-texto="Caja chica" />

<div class="stats">
    <x-stat label="Monto" :valor="'S/ '.number_format($movimiento->monto, 2)" tono="rojo" />
</div>
<div class="card p-3">
    <dl class="ficha mb-0">
        <dt>Fecha</dt><dd>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</dd>
        <dt>Proveedor</dt><dd>{{ $movimiento->proveedor ?: '—' }}</dd>
        <dt>Descripción</dt><dd>{{ $movimiento->descripcion }}</dd>
        <dt>Registrado</dt><dd>{{ optional($movimiento->created_at)->format('d/m/Y H:i') ?? '—' }}</dd>
    </dl>
</div>
@endsection
