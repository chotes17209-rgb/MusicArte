@extends('layouts.pdf')
@section('titulo', 'Recibo de abono')
@section('subtitulo', 'N° '.($abono->recibo_nro ?? $abono->id).' · '.$abono->fecha->format('d/m/Y'))

@section('estilos')
    .ficha td { padding: 5px 0; border-bottom: 1px solid #ecebe7; }
    .ficha td:first-child { color: #8a8880; width: 34%; }
    .destacado { margin: 14px 0; padding: 12px; border: 1.5px solid #800080; background: #faf1fa; text-align: center; }
    .destacado .etq { font-size: 8.5px; color: #55534d; text-transform: uppercase; letter-spacing: .4px; }
    .destacado .val { font-size: 22px; font-weight: bold; color: #800080; margin-top: 2px; }
    .montos td { width: 50%; padding: 8px 10px; border: 1px solid #e6e5e0; background: #fafaf8; }
    .montos .etq { font-size: 8px; color: #8a8880; text-transform: uppercase; }
    .montos .val { font-size: 13px; font-weight: bold; margin-top: 2px; }
@endsection

@section('contenido')
<div class="destacado">
    <div class="etq">Monto recibido</div>
    <div class="val">S/ {{ number_format($abono->monto, 2) }}</div>
    <div class="muted">{{ $abono->metodoLabel() }}</div>
</div>

<table class="ficha">
    <tr><td>Alumno</td><td class="fuerte">{{ $abono->pago->alumno->nombre }}</td></tr>
    <tr><td>{{ $abono->pago->esMatricula() ? 'Concepto' : 'Taller' }}</td><td>{{ $abono->pago->esMatricula() ? $abono->pago->tallerLabel() : ($abono->pago->alumnoTaller->especialidad->nombre ?? ($abono->pago->alumno->especialidad->nombre ?? '—')) }}</td></tr>
    <tr><td>Concepto</td><td>{{ $abono->pago->concepto ?? 'Mensualidad' }}</td></tr>
    <tr><td>Periodo</td><td>{{ $abono->pago->mesLabel() }} {{ $abono->pago->anio }}</td></tr>
    <tr><td>Fecha del abono</td><td>{{ $abono->fecha->format('d/m/Y') }}</td></tr>
    @if($abono->observacion)<tr><td>Observación</td><td>{{ $abono->observacion }}</td></tr>@endif
</table>

<table class="montos" style="margin-top:14px"><tr>
    <td><div class="etq">Total del mes</div><div class="val">S/ {{ number_format($abono->pago->monto_total, 2) }}</div></td>
    <td><div class="etq">Saldo después de este abono</div><div class="val {{ $abono->pago->saldo > 0 ? 'rojo' : 'verde' }}">S/ {{ number_format($abono->pago->saldo, 2) }}</div></td>
</tr></table>
@endsection
