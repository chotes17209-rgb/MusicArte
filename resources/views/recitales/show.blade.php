@extends('layouts.app')
@section('titulo', 'Recital o evento')

@section('contenido')
<x-page-head :titulo="$recital->nombre" :subtitulo="$recital->fecha ? \Carbon\Carbon::parse($recital->fecha)->translatedFormat('l d \d\e F \d\e Y') : null" :volver="route('recitales.index')" volver-texto="Recitales" />

<div class="card p-3">
    <dl class="ficha mb-0">
        <dt>Tema</dt><dd>{{ $recital->tema ?: '—' }}</dd>
        <dt>Pago por alumno</dt><dd>{{ $recital->pago_por_alumno !== null ? 'S/ '.number_format($recital->pago_por_alumno, 2) : '—' }}</dd>
        <dt>Participantes</dt><dd style="white-space: pre-line">{{ $recital->participantes ?: '—' }}</dd>
        <dt>Descripción</dt><dd style="white-space: pre-line">{{ $recital->descripcion ?: '—' }}</dd>
    </dl>
</div>
@endsection
