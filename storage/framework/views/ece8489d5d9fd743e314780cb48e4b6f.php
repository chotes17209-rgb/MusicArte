<?php $__env->startSection('titulo', 'Dashboard'); ?>

<?php $__env->startSection('contenido'); ?>


<?php if($alertaSunat['mostrar']): ?>
<div class="alert <?php echo e($alertaSunat['es_hoy'] ? 'alert-danger' : 'alert-warning'); ?> d-flex align-items-center gap-2 shadow-sm mb-4" style="border-left: 6px solid <?php echo e($alertaSunat['es_hoy'] ? '#dc3545' : '#c99a06'); ?>;">
    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
    <div>
        <?php if($alertaSunat['es_hoy']): ?>
            <div class="fw-bold fs-5">⚠️ HOY ES 14</div>
        <?php else: ?>
            <div class="fw-bold fs-5">⚠️ FALTAN <?php echo e($alertaSunat['dias_restantes']); ?> <?php echo e($alertaSunat['dias_restantes'] == 1 ? 'DIA' : 'DIAS'); ?> PARA EL 14</div>
        <?php endif; ?>
        <div>Se debe realizar el pago de SUNAT y servicios.</div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-kpi p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Alumnos activos</div>
                    <div class="fs-3 fw-bold" style="color:#3d2c8d"><?php echo e($kpis['alumnos_activos']); ?></div>
                </div>
                <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-people fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-kpi p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Maestros activos</div>
                    <div class="fs-3 fw-bold" style="color:#3d2c8d"><?php echo e($kpis['maestros_activos']); ?></div>
                </div>
                <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-person-badge fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-kpi p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Clases hoy</div>
                    <div class="fs-3 fw-bold" style="color:#3d2c8d"><?php echo e($kpis['clases_hoy']); ?></div>
                    <div class="small text-success"><?php echo e($kpis['clases_hoy_realizadas']); ?> realizadas</div>
                </div>
                <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-calendar3 fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?php echo e(route('pagos.index')); ?>" class="text-decoration-none">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Pagos</div>
                        <div class="fs-6 fw-semibold" style="color:#3d2c8d">Ver modulo &rarr;</div>
                        <div class="small text-muted">Informacion financiera aqui</div>
                    </div>
                    <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-cash-coin fs-5"></i></div>
                </div>
            </div>
        </a>
    </div>
</div>


<div class="card p-3 mb-4">
    <h6 class="fw-semibold mb-3"><i class="bi bi-bar-chart-line me-1"></i> Alumnos por mes</h6>
    <?php if($alumnosPorMes->isEmpty()): ?>
        <p class="text-muted small mb-0">Aun no hay periodos registrados.</p>
    <?php else: ?>
        <?php $maxCant = max(1, $alumnosPorMes->max('cantidad')); ?>
        <div class="d-flex align-items-end gap-3" style="height:140px;">
            <?php $__currentLoopData = $alumnosPorMes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="d-flex flex-column align-items-center justify-content-end" style="flex:1; height:100%;">
                    <div class="small fw-semibold mb-1"><?php echo e($m['cantidad']); ?></div>
                    <div style="width:100%; max-width:48px; background:#3d2c8d; border-radius:6px 6px 0 0; height:<?php echo e(max(4, round($m['cantidad'] / $maxCant * 100))); ?>%;"></div>
                    <div class="small text-muted mt-1 text-center"><?php echo e($m['label']); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <h6 class="fw-semibold mb-3"><i class="bi bi-calendar-event me-1"></i> Clases de hoy</h6>
            <?php if($clasesHoy->isEmpty()): ?>
                <p class="text-muted small mb-0">No hay clases programadas para hoy.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Hora</th><th>Alumno</th><th>Maestro</th><th>Especialidad</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $clasesHoy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($c->hora_inicio)->format('H:i')); ?></td>
                                <td><?php echo e($c->alumno->nombre); ?></td>
                                <td><?php echo e($c->maestro->nombre ?? '—'); ?></td>
                                <td><?php echo e($c->especialidad->nombre ?? '—'); ?></td>
                                <td>
                                    <span class="badge <?php echo e($c->estado === 'realizada' ? 'bg-success' : ($c->estado === 'cancelada' ? 'bg-danger' : 'bg-secondary')); ?>"><?php echo e(ucfirst($c->estado)); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <a href="<?php echo e(route('calendario.index')); ?>" class="small mt-2">Ir al calendario completo &rarr;</a>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-cash-stack me-1"></i> Alumnos con pago pendiente (mes actual)</h6>
            <?php if($alumnosConSaldo->isEmpty()): ?>
                <p class="text-muted small mb-0">No hay pagos pendientes registrados.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $alumnosConSaldo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><?php echo e($a->nombre); ?></span>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <a href="<?php echo e(route('pagos.index')); ?>" class="small mt-2">Ver detalle de montos en Pagos &rarr;</a>
            <?php endif; ?>
        </div>

        <?php if($cumpleanieros->isNotEmpty()): ?>
        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-balloon-heart-fill me-1"></i> Cumpleanos del mes</h6>
            <ul class="list-group list-group-flush">
                <?php $__currentLoopData = $cumpleanieros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span><?php echo e($c->nombre); ?></span>
                        <span class="text-muted small"><?php echo e(\Carbon\Carbon::parse($c->fecha_nacimiento)->format('d/m')); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/dashboard.blade.php ENDPATH**/ ?>