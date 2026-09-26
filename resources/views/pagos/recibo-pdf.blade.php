@extends('layouts.pdf')
@section('titulo', 'Estado de cuenta')
@section('subtitulo', 'Pago N° '.$pago->id.' · '.$pago->mesLabel().' '.$pago->anio)

@section('estilos')
    .ficha td { padding: 5px 0; border-bottom: 1px solid #ecebe7; }
    .ficha td:first-child { color: #8a8880; width: 34%; }
    .montos { margin: 14px 0; }
    .montos td { width: 33.3%; padding: 8px 10px; border: 1px solid #e6e5e0; background: #fafaf8; }
    .montos .etq { font-size: 8px; color: #8a8880; text-transform: uppercase; }
    .montos .val { font-size: 14px; font-weight: bold; margin-top: 2px; }
@endsection

@section('contenido')
<table class="ficha">
    <tr><td>Alumno</td><td class="fuerte">{{ $pago->alumno->nombre }}</td></tr>
    <tr><td>Taller</td><td>{{ $pago->alumnoTaller->especialidad->nombre ?? ($pago->alumno->especialidad->nombre ?? '—') }}@if($pago->alumnoTaller?->maestro) <span class="muted">· {{ $pago->alumnoTaller->maestro->nombre }}</span>@endif</td></tr>
    <tr><td>Concepto</td><td>{{ $pago->concepto ?? 'Mensualidad' }}</td></tr>
    <tr><td>Periodo</td><td>{{ $pago->mesLabel() }} {{ $pago->anio }}</td></tr>
    <tr><td>Estado</td><td><span class="estado {{ $pago->estado }}">{{ $pago->estadoLabel() }}</span></td></tr>
</table>

<table class="montos"><tr>
    <td><div class="etq">Monto total</div><div class="val">S/ {{ number_format($pago->monto_total, 2) }}</div></td>
    <td><div class="etq">Abonado</div><div class="val verde">S/ {{ number_format($pago->montoAbonado(), 2) }}</div></td>
    <td><div class="etq">Saldo</div><div class="val {{ $pago->saldo > 0 ? 'rojo' : 'verde' }}">S/ {{ number_format($pago->saldo, 2) }}</div></td>
</tr></table>

<h2>Detalle de abonos</h2>
<table class="tabla">
    <thead><tr><th>Fecha</th><th>Método</th><th>N° recibo</th><th class="der">Monto</th></tr></thead>
    <tbody>
    @forelse($pago->abonos as $ab)
        <tr><td>{{ $ab->fecha->format('d/m/Y') }}</td><td>{{ $ab->metodoLabel() }}</td><td>{{ $ab->recibo_nro ?? '—' }}</td><td class="der">S/ {{ number_format($ab->monto, 2) }}</td></tr>
    @empty
        <tr><td colspan="4" class="centro muted">Aún no se ha registrado ningún abono.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
