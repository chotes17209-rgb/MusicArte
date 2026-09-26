@extends('layouts.app')
@section('titulo', 'Pagos del año')

@section('contenido')
<x-page-head titulo="Pagos del año {{ $anio }}"
             subtitulo="Mes a mes: cuánto se cobró de lo que correspondía, y quiénes todavía deben."
             :volver="route('reportes.index')" volver-texto="Reportes">
    <form method="GET">
        <input type="number" name="anio" value="{{ $anio }}" class="form-control" style="width:96px" onchange="this.form.submit()">
    </form>
    <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
</x-page-head>

@php $cobranza = $totales['facturado'] > 0 ? round($totales['cobrado'] / $totales['facturado'] * 100, 1) : null; @endphp
<div class="stats">
    <x-stat label="Por cobrar en el año" :valor="'S/ '.number_format($totales['facturado'], 2)" />
    <x-stat label="Cobrado" :valor="'S/ '.number_format($totales['cobrado'], 2)" tono="verde" :detalle="$cobranza !== null ? $cobranza.'% de lo que correspondía' : null" />
    <x-stat label="Pendiente" :valor="'S/ '.number_format($totales['pendiente'], 2)" tono="rojo" :detalle="$deudores->count().' alumnos deben'" />
</div>

<div class="card p-3 mb-3">
    <h6 class="seccion-titulo">Detalle por mes</h6>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Mes</th><th class="text-end">Por cobrar</th><th class="text-end">Cobrado</th><th class="text-end">Pendiente</th>
                    <th style="min-width:170px">Cobranza</th>
                    <th class="text-end">Pagados</th><th class="text-end">A cuenta</th><th class="text-end">Sin pagar</th>
                </tr>
            </thead>
            <tbody>
            @foreach($porMes as $m)
                @continue($m['facturado'] == 0 && $m['mes'] > now()->month && $anio == now()->year)
                <tr>
                    <td class="fw-semibold">{{ $m['label'] }}</td>
                    <td class="text-end">S/ {{ number_format($m['facturado'], 2) }}</td>
                    <td class="text-end tono-verde">S/ {{ number_format($m['cobrado'], 2) }}</td>
                    <td class="text-end {{ $m['pendiente'] > 0 ? 'tono-rojo' : 'text-muted' }}">S/ {{ number_format($m['pendiente'], 2) }}</td>
                    <td>
                        @if($m['facturado'] > 0)
                            <x-barra :porcentaje="$m['cobrado'] / $m['facturado'] * 100" />
                        @else
                            <span class="text-muted small">Sin pagos</span>
                        @endif
                    </td>
                    <td class="text-end">{{ $m['cant_pagado'] }}</td>
                    <td class="text-end">{{ $m['cant_a_cuenta'] ?: '—' }}</td>
                    <td class="text-end {{ $m['cant_pendiente'] > 0 ? 'tono-rojo' : 'text-muted' }}">{{ $m['cant_pendiente'] ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card p-3">
    <h6 class="seccion-titulo">Alumnos que deben ({{ $deudores->count() }})</h6>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Meses con saldo</th><th class="text-end">Total que debe</th></tr></thead>
            <tbody>
            @forelse($deudores as $d)
                <tr>
                    <td class="fw-semibold">{{ $d['alumno'] ?? '—' }}</td>
                    <td>{{ $d['meses_pendientes'] }}</td>
                    <td class="text-end tono-rojo fw-semibold">S/ {{ number_format($d['total_debe'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3"><div class="vacio"><i class="bi bi-check-circle"></i>Nadie debe en {{ $anio }}.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
