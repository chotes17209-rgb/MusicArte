<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #222; padding: 10px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { color: #3d2c8d; margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td, th { padding: 6px 4px; border-bottom: 1px solid #ddd; text-align: left; }
        .total { font-size: 15px; font-weight: bold; color: #3d2c8d; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #777; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .badge-pagado { background: #d1f7dd; color: #1a7a3f; }
        .badge-a_cuenta { background: #fff3cd; color: #8a6d00; }
        .badge-pendiente { background: #f8d7da; color: #a12631; }
    </style>
</head>
<body>
    <div class="header">
        <h2>MUSICA ARTE - CENTRO CULTURAL</h2>
        <div>RESUMEN DE PAGO N° {{ $pago->id }}</div>
    </div>

    <table>
        <tr><th>Alumno</th><td>{{ $pago->alumno->nombre }}</td></tr>
        <tr><th>Taller</th><td>{{ $pago->especialidad->nombre ?? '—' }}</td></tr>
        <tr><th>Maestro</th><td>{{ $pago->maestro->nombre ?? '—' }}</td></tr>
        <tr><th>Concepto</th><td>{{ $pago->concepto ?? 'Mensualidad' }}</td></tr>
        <tr><th>Periodo</th><td>{{ $pago->periodo->nombre ?? ($pago->mesLabel().' '.$pago->anio) }}</td></tr>
        <tr><th>Estado</th><td><span class="badge badge-{{ $pago->estado }}">{{ $pago->estadoLabel() }}</span></td></tr>
    </table>

    <table>
        <tr><th class="total">Monto Total</th><td class="total">S/ {{ number_format($pago->monto_total, 2) }}</td></tr>
        <tr><th>Monto Pagado</th><td>S/ {{ number_format($pago->monto_pagado, 2) }}</td></tr>
        <tr><th>Saldo pendiente</th><td>S/ {{ number_format($pago->saldo, 2) }}</td></tr>
    </table>

    <h4 style="color:#3d2c8d">Detalle de abonos</h4>
    <table>
        <tr><th>Fecha</th><th>Metodo</th><th>N° Recibo</th><th>Monto</th></tr>
        @forelse($pago->abonos as $a)
        <tr>
            <td>{{ $a->fecha->format('d/m/Y') }}</td>
            <td>{{ $a->metodoLabel() }}</td>
            <td>{{ $a->recibo_nro ?? '—' }}</td>
            <td>S/ {{ number_format($a->monto, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="4">Sin abonos registrados.</td></tr>
        @endforelse
    </table>

    <div class="footer">
        Documento generado por el sistema de gestion Musica Arte &mdash; {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
