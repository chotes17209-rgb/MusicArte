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
    </style>
</head>
<body>
    <div class="header">
        <h2>MUSICA ARTE - CENTRO CULTURAL</h2>
        <div>RECIBO DE PAGO N° <?php echo e($pago->recibo_nro ?? $pago->id); ?></div>
    </div>

    <table>
        <tr><th>Alumno</th><td><?php echo e($pago->alumno->nombre); ?></td></tr>
        <tr><th>Especialidad</th><td><?php echo e($pago->alumno->especialidad->nombre ?? '—'); ?></td></tr>
        <tr><th>Concepto</th><td><?php echo e($pago->concepto ?? 'Mensualidad'); ?></td></tr>
        <tr><th>Periodo</th><td><?php echo e($pago->mesLabel()); ?> <?php echo e($pago->anio); ?></td></tr>
        <tr><th>Fecha de pago</th><td><?php echo e(optional($pago->fecha_pago)->format('d/m/Y') ?? '—'); ?></td></tr>
    </table>

    <table>
        <tr><th>Yape / Transferencia</th><td>S/ <?php echo e(number_format($pago->yape_transferencia, 2)); ?></td></tr>
        <tr><th>Efectivo</th><td>S/ <?php echo e(number_format($pago->efectivo, 2)); ?></td></tr>
        <tr><th>Tarjeta</th><td>S/ <?php echo e(number_format($pago->tarjeta, 2)); ?></td></tr>
        <tr><th class="total">Monto Total</th><td class="total">S/ <?php echo e(number_format($pago->monto_total, 2)); ?></td></tr>
        <tr><th>Saldo pendiente</th><td>S/ <?php echo e(number_format($pago->saldo, 2)); ?></td></tr>
    </table>

    <div class="footer">
        Documento generado por el sistema de gestion Musica Arte &mdash; <?php echo e(now()->format('d/m/Y H:i')); ?>

    </div>
</body>
</html>
<?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/pagos/recibo-pdf.blade.php ENDPATH**/ ?>