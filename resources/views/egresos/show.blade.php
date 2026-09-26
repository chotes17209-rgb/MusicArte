@extends('layouts.app')
@section('titulo', 'Egreso')

@section('contenido')
<x-page-head :titulo="$egreso->detalle" :subtitulo="'Egreso del '.\Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y')" :volver="route('egresos.index')" volver-texto="Egresos" />

<div class="stats">
    <x-stat label="Total" :valor="'S/ '.number_format($egreso->total, 2)" tono="rojo" />
</div>
<div class="card p-3">
    <h6 class="seccion-titulo">Cómo se pagó</h6>
    <dl class="ficha mb-0">
        <dt>Yape / BCP</dt><dd>S/ {{ number_format($egreso->yape_bcp, 2) }}</dd>
        <dt>Plin / Interbank</dt><dd>S/ {{ number_format($egreso->plin_ibk, 2) }}</dd>
        <dt>Tarjeta</dt><dd>S/ {{ number_format($egreso->tarjeta, 2) }}</dd>
        <dt>Efectivo</dt><dd>S/ {{ number_format($egreso->efectivo, 2) }}</dd>
        <dt>Registrado</dt><dd>{{ optional($egreso->created_at)->format('d/m/Y H:i') ?? '—' }}</dd>
    </dl>
</div>
@endsection
