@extends('layouts.pdf')
@section('titulo', 'Pagos del año')
@section('subtitulo', 'Año '.$anio)

@section('contenido')
@php $cobranza = $totales['facturado'] > 0 ? round($totales['cobrado'] / $totales['facturado'] * 100, 1) : null; @endphp
<table class="resumen"><tr>
    <td><div class="etq">Facturado</div><div class="val">S/ {{ number_format($totales['facturado'], 2) }}</div><div class="det">total de mensualidades</div></td>
    <td><div class="etq">Cobrado</div><div class="val verde">S/ {{ number_format($totales['cobrado'], 2) }}</div><div class="det">{{ $cobranza !== null ? $cobranza.'% de lo facturado' : '' }}</div></td>
    <td><div class="etq">Pendiente</div><div class="val rojo">S/ {{ number_format($totales['pendiente'], 2) }}</div><div class="det">por cobrar</div></td>
    <td><div class="etq">Deudores</div><div class="val">{{ $deudores->count() }}</div><div class="det">alumnos con saldo</div></td>
</tr></table>

<h2>Mes a mes</h2>
<table class="tabla">
    <thead><tr><th>Mes</th><th class="der">Facturado</th><th class="der">Cobrado</th><th class="der">Pendiente</th><th class="der">Pagados</th><th class="der">A cuenta</th><th class="der">Sin pagar</th><th style="width:18%">Cobranza</th></tr></thead>
    <tbody>
    @foreach($porMes->filter(fn ($m) => $m['facturado'] > 0) as $m)
        @php $pct = round($m['cobrado'] / $m['facturado'] * 100); @endphp
        <tr>
            <td class="fuerte">{{ $m['label'] }}</td>
            <td class="der">S/ {{ number_format($m['facturado'], 2) }}</td>
            <td class="der verde">S/ {{ number_format($m['cobrado'], 2) }}</td>
            <td class="der {{ $m['pendiente'] > 0 ? 'rojo' : 'muted' }}">S/ {{ number_format($m['pendiente'], 2) }}</td>
            <td class="der">{{ $m['cant_pagado'] }}</td>
            <td class="der">{{ $m['cant_a_cuenta'] ?: '' }}</td>
            <td class="der">{{ $m['cant_pendiente'] ?: '' }}</td>
            <td><div class="barra"><div style="width: {{ $pct }}%; background:#1d7a46"></div></div><span class="muted">{{ $pct }}%</span></td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr><td>Total</td><td class="der">S/ {{ number_format($totales['facturado'], 2) }}</td><td class="der">S/ {{ number_format($totales['cobrado'], 2) }}</td><td class="der">S/ {{ number_format($totales['pendiente'], 2) }}</td><td class="der">{{ $porMes->sum('cant_pagado') }}</td><td class="der">{{ $porMes->sum('cant_a_cuenta') }}</td><td class="der">{{ $porMes->sum('cant_pendiente') }}</td><td></td></tr></tfoot>
</table>

<h2>Alumnos con deuda</h2>
<table class="tabla">
    <thead><tr><th>Alumno</th><th>Meses con saldo</th><th class="der">Debe</th></tr></thead>
    <tbody>
    @forelse($deudores as $d)
        <tr><td class="fuerte">{{ $d['alumno'] ?? '—' }}</td><td class="muted">{{ $d['meses_pendientes'] }}</td><td class="der fuerte rojo">S/ {{ number_format($d['total_debe'], 2) }}</td></tr>
    @empty
        <tr><td colspan="3" class="centro muted">Nadie debe en {{ $anio }}.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
