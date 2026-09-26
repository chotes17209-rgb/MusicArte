@extends('layouts.pdf')
@section('titulo', 'Ingresos y egresos')
@section('subtitulo', 'Año '.$anio)

@section('contenido')
@php
    $ing = $data->sum('ingresos'); $egr = $data->sum('egresos'); $bal = $ing - $egr;
    $max = max(1, $data->max('ingresos'), $data->max('egresos'));
    $conMovimiento = $data->filter(fn ($m) => $m['ingresos'] > 0 || $m['egresos'] > 0);
@endphp
<table class="resumen"><tr>
    <td><div class="etq">Ingresos</div><div class="val verde">S/ {{ number_format($ing, 2) }}</div><div class="det">lo cobrado a alumnos</div></td>
    <td><div class="etq">Egresos</div><div class="val rojo">S/ {{ number_format($egr, 2) }}</div><div class="det">gastos + caja chica + planilla</div></td>
    <td><div class="etq">Balance</div><div class="val {{ $bal >= 0 ? 'verde' : 'rojo' }}">S/ {{ number_format($bal, 2) }}</div><div class="det">ingresos − egresos</div></td>
    <td><div class="etq">Promedio mensual</div><div class="val">S/ {{ number_format($conMovimiento->count() ? $bal / $conMovimiento->count() : 0, 2) }}</div><div class="det">de balance, {{ $conMovimiento->count() }} meses con movimiento</div></td>
</tr></table>

<table class="tabla">
    <thead><tr><th>Mes</th><th class="der">Ingresos</th><th class="der">Egresos</th><th class="der">Balance</th><th style="width:34%">Ingresos <span style="color:#b8e0c8">■</span> / egresos <span style="color:#f2b8b0">■</span></th></tr></thead>
    <tbody>
    @foreach($data as $m)
        <tr>
            <td class="fuerte">{{ $m['mes'] }}</td>
            <td class="der">{{ $m['ingresos'] ? 'S/ '.number_format($m['ingresos'], 2) : '—' }}</td>
            <td class="der">{{ $m['egresos'] ? 'S/ '.number_format($m['egresos'], 2) : '—' }}</td>
            <td class="der fuerte {{ $m['balance'] > 0 ? 'verde' : ($m['balance'] < 0 ? 'rojo' : 'muted') }}">{{ $m['balance'] ? 'S/ '.number_format($m['balance'], 2) : '—' }}</td>
            <td>
                <div class="barra" style="margin-bottom:2px"><div style="width: {{ round($m['ingresos'] / $max * 100) }}%; background:#3fa46a"></div></div>
                <div class="barra"><div style="width: {{ round($m['egresos'] / $max * 100) }}%; background:#d9534f"></div></div>
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr><td>Total {{ $anio }}</td><td class="der">S/ {{ number_format($ing, 2) }}</td><td class="der">S/ {{ number_format($egr, 2) }}</td><td class="der">S/ {{ number_format($bal, 2) }}</td><td></td></tr></tfoot>
</table>
@endsection
