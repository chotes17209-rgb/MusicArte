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
        .total { font-size: 16px; font-weight: bold; color: #3d2c8d; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h2>MUSICA ARTE - CENTRO CULTURAL</h2>
        <div>RECIBO DE ABONO N° {{ $abono->recibo_nro ?? $abono->id }}</div>
    </div>

    <table>
        <tr><th>Alumno</th><td>{{ $pago->alumno->nombre }}</td></tr>
        <tr><th>Taller</th><td>{{ $pago->especialidad->nombre ?? '—' }}</td></tr>
        <tr><th>Concepto</th><td>{{ $pago->concepto ?? 'Mensualidad' }}</td></tr>
        <tr><th>Periodo</th><td>{{ $pago->periodo->nombre ?? ($pago->mesLabel().' '.$pago->anio) }}</td></tr>
        <tr><th>Fecha de abono</th><td>{{ $abono->fecha->format('d/m/Y') }}</td></tr>
        <tr><th>Metodo de pago</th><td>{{ $abono->metodoLabel() }}</td></tr>
        <tr><th>Registrado por</th><td>{{ $abono->usuario->name ?? '—' }}</td></tr>
    </table>

    <table>
        <tr><th class="total">Monto de este abono</th><td class="total">S/ {{ number_format($abono->monto, 2) }}</td></tr>
        <tr><th>Monto total de la deuda</th><td>S/ {{ number_format($pago->monto_total, 2) }}</td></tr>
        <tr><th>Saldo pendiente despues de este abono</th><td>S/ {{ number_format($pago->saldo, 2) }}</td></tr>
    </table>

    <div class="footer">
        Documento generado por el sistema de gestion Musica Arte &mdash; {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
