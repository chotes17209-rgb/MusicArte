<?php $__env->startSection('titulo', 'Pagos'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Pagos / Mensualidades</h5>
        <small class="text-muted"><?php echo e(\App\Models\Pago::MESES[$mes]); ?> <?php echo e($anio); ?></small>
    </div>
    <?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalPago" onclick="nuevoPago()"><i class="bi bi-plus-lg me-1"></i> Registrar Pago</button>
    <?php else: ?>
    <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Modo solo lectura (precios reservados al administrador)</span>
    <?php endif; ?> <?php endif; ?>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Recaudado del mes</div><div class="fs-4 fw-bold text-success">S/ <?php echo e(number_format($totales['recaudado'], 2)); ?></div></div></div>
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Pendiente de cobro</div><div class="fs-4 fw-bold text-danger">S/ <?php echo e(number_format($totales['pendiente'], 2)); ?></div></div></div>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-3">
            <select name="mes" class="form-select">
                <?php $__currentLoopData = \App\Models\Pago::MESES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $nombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($num); ?>" <?php if($mes==$num): echo 'selected'; endif; ?>><?php echo e($nombre); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="anio" value="<?php echo e($anio); ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="text" name="buscar" value="<?php echo e(request('buscar')); ?>" class="form-control" placeholder="Buscar alumno...">
        </div>
        <div class="col-md-2 d-flex align-items-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="solo_pendientes" value="1" id="solo_pendientes" <?php echo e(request('solo_pendientes') ? 'checked' : ''); ?>>
                <label class="form-check-label small" for="solo_pendientes">Solo pendientes</label>
            </div>
        </div>
        <div class="col-md-2"><button class="btn btn-light w-100">Filtrar</button></div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Concepto</th><th>Total</th><th>Yape/Transf.</th><th>Efectivo</th><th>Tarjeta</th><th>Saldo</th><th>Fecha pago</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $pagos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($p->alumno->nombre ?? '—'); ?></td>
                    <td><?php echo e($p->concepto ?? '—'); ?></td>
                    <td>S/ <?php echo e(number_format($p->monto_total, 2)); ?></td>
                    <td>S/ <?php echo e(number_format($p->yape_transferencia, 2)); ?></td>
                    <td>S/ <?php echo e(number_format($p->efectivo, 2)); ?></td>
                    <td>S/ <?php echo e(number_format($p->tarjeta, 2)); ?></td>
                    <td>
                        <?php if($p->saldo > 0): ?><span class="text-danger fw-semibold">S/ <?php echo e(number_format($p->saldo, 2)); ?></span>
                        <?php else: ?><span class="text-success">Pagado</span><?php endif; ?>
                    </td>
                    <td><?php echo e(optional($p->fecha_pago)->format('d/m/Y') ?? '—'); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('pagos.recibo', $p)); ?>" target="_blank" class="btn btn-sm btn-light btn-icon" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?>
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarPago(<?php echo e($p->id); ?>)"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarPago(<?php echo e($p->id); ?>, '<?php echo e($p->alumno->nombre ?? ''); ?>')"><i class="bi bi-trash"></i></button>
                        <?php endif; ?> <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">No hay pagos registrados para este periodo.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo e($pagos->links()); ?>

</div>

<?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?>
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formPago">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalPago">Registrar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pago_id">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Alumno</label>
                        <select class="form-select" id="pago_alumno_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php $__currentLoopData = $alumnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>"><?php echo e($a->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Concepto</label>
                        <input type="text" class="form-control" id="pago_concepto" placeholder="Ej. Mensualidad Piano">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Mes</label>
                        <select class="form-select" id="pago_mes">
                            <?php $__currentLoopData = \App\Models\Pago::MESES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $nombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($num); ?>" <?php if($mes==$num): echo 'selected'; endif; ?>><?php echo e($nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Ano</label>
                        <input type="number" class="form-control" id="pago_anio" value="<?php echo e($anio); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Fecha de pago</label>
                        <input type="date" class="form-control" id="pago_fecha_pago">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-semibold">Monto total (S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="pago_monto_total" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-semibold">Yape/Transf.</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="pago_yape">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-semibold">Efectivo</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="pago_efectivo">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-semibold">Tarjeta</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="pago_tarjeta">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">N° de recibo</label>
                    <input type="text" class="form-control" id="pago_recibo_nro">
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Observacion</label>
                    <textarea class="form-control" id="pago_observacion" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?> <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
<?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?>
    const modalPago = new bootstrap.Modal('#modalPago');

    function nuevoPago() {
        document.getElementById('formPago').reset();
        document.getElementById('pago_id').value = '';
        document.getElementById('tituloModalPago').innerText = 'Registrar Pago';
    }

    async function editarPago(id) {
        const res = await maFetch(`/pagos/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('pago_id').value = d.id;
        document.getElementById('pago_alumno_id').value = d.alumno_id;
        document.getElementById('pago_concepto').value = d.concepto ?? '';
        document.getElementById('pago_mes').value = d.mes;
        document.getElementById('pago_anio').value = d.anio;
        document.getElementById('pago_fecha_pago').value = d.fecha_pago ? d.fecha_pago.substring(0,10) : '';
        document.getElementById('pago_monto_total').value = d.monto_total;
        document.getElementById('pago_yape').value = d.yape_transferencia;
        document.getElementById('pago_efectivo').value = d.efectivo;
        document.getElementById('pago_tarjeta').value = d.tarjeta;
        document.getElementById('pago_recibo_nro').value = d.recibo_nro ?? '';
        document.getElementById('pago_observacion').value = d.observacion ?? '';
        document.getElementById('tituloModalPago').innerText = 'Editar Pago';
        modalPago.show();
    }

    document.getElementById('formPago').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('pago_id').value;
        const payload = {
            alumno_id: document.getElementById('pago_alumno_id').value,
            concepto: document.getElementById('pago_concepto').value,
            mes: document.getElementById('pago_mes').value,
            anio: document.getElementById('pago_anio').value,
            fecha_pago: document.getElementById('pago_fecha_pago').value || null,
            monto_total: document.getElementById('pago_monto_total').value || 0,
            yape_transferencia: document.getElementById('pago_yape').value || 0,
            efectivo: document.getElementById('pago_efectivo').value || 0,
            tarjeta: document.getElementById('pago_tarjeta').value || 0,
            recibo_nro: document.getElementById('pago_recibo_nro').value,
            observacion: document.getElementById('pago_observacion').value,
        };
        const url = id ? `/pagos/${id}` : '/pagos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalPago.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarPago(id, nombre) {
        if (!(await maConfirmarEliminar(`el pago de ${nombre}`))) return;
        const res = await maFetch(`/pagos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
<?php endif; ?> <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/pagos/index.blade.php ENDPATH**/ ?>