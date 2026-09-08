<?php $__env->startSection('titulo', 'Perfil del alumno'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="<?php echo e(route('alumnos.index')); ?>" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Alumnos</a>
        <h4 class="fw-bold mb-0 mt-1" style="color:#3d2c8d"><?php echo e($alumno->nombre); ?></h4>
        <div class="d-flex gap-2 mt-1 flex-wrap">
            <?php if($alumno->activo): ?>
                <span class="badge bg-success">Activo</span>
            <?php else: ?>
                <span class="badge bg-secondary">Inactivo</span>
            <?php endif; ?>
            <?php if($alumno->edad !== null): ?><span class="badge bg-light text-dark border"><?php echo e($alumno->edad); ?> años</span><?php endif; ?>
            <?php if($alumno->dni): ?><span class="badge bg-light text-dark border">DNI <?php echo e($alumno->dni); ?></span><?php endif; ?>
        </div>
    </div>
    <button class="btn btn-morado" onclick="editarAlumnoDesdeShow(<?php echo e($alumno->id); ?>)"><i class="bi bi-pencil me-1"></i> Editar</button>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-person-vcard me-1"></i> Datos del alumno</h6>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">Tutor</dt><dd class="col-7"><?php echo e($alumno->tutor ?? '—'); ?></dd>
                <dt class="col-5 text-muted">Celular</dt><dd class="col-7"><?php echo e($alumno->celular ?? '—'); ?></dd>
                <dt class="col-5 text-muted">Fecha nacimiento</dt><dd class="col-7"><?php echo e(optional($alumno->fecha_nacimiento)->format('d/m/Y') ?? '—'); ?></dd>
                <dt class="col-5 text-muted">Fecha ingreso</dt><dd class="col-7"><?php echo e(optional($alumno->fecha_ingreso)->format('d/m/Y') ?? '—'); ?></dd>
                <?php if($alumno->diagnostico): ?>
                <dt class="col-5 text-muted">Diagnostico</dt><dd class="col-7"><?php echo e($alumno->diagnostico); ?></dd>
                <?php endif; ?>
                <?php if($alumno->observaciones): ?>
                <dt class="col-5 text-muted">Observaciones</dt><dd class="col-7"><?php echo e($alumno->observaciones); ?></dd>
                <?php endif; ?>
            </dl>
        </div>

        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-music-note-list me-1"></i> Talleres activos ahora</h6>
            <?php $__empty_1 = true; $__currentLoopData = $tallerActual; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="border rounded p-2 mb-2">
                    <div class="fw-semibold"><?php echo e($t->especialidad->nombre ?? '—'); ?></div>
                    <div class="small text-muted">
                        <i class="bi bi-person-badge"></i> <?php echo e($t->maestro->nombre ?? 'Sin maestro asignado'); ?>

                        <?php if($t->periodo): ?><br><i class="bi bi-calendar3"></i> <?php echo e($t->periodo->nombre); ?><?php endif; ?>
                        <?php if($t->salon): ?><br><i class="bi bi-door-open"></i> Salón <?php echo e($t->salon); ?><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted small mb-0">Sin talleres activos actualmente.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-1"><i class="bi bi-clock-history me-1"></i> Línea de tiempo por periodo</h6>
            <small class="text-muted d-block mb-3">Con qué maestro, taller y horario estuvo cada mes — util para reincorporarlo con el mismo maestro.</small>

            <?php $__empty_1 = true; $__currentLoopData = $lineaDeTiempo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="timeline-item mb-3 pb-3 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="fw-semibold"><?php echo e($item['periodo']->nombre); ?></div>
                        <?php if($item['estado'] === 'activo'): ?>
                            <span class="badge bg-success">Activo este periodo</span>
                        <?php elseif($item['estado'] === 'inactivo'): ?>
                            <span class="badge bg-secondary">Inactivo este periodo</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">Sin registro de estado</span>
                        <?php endif; ?>
                    </div>

                    <?php if($item['horarios']->isEmpty()): ?>
                        <p class="text-muted small mb-0">No hay horario detallado registrado para este periodo.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Taller</th><th>Maestro</th><th>Día</th><th>Hora</th><th>Salón</th><th></th></tr></thead>
                                <tbody>
                                <?php $__currentLoopData = $item['horarios']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e(!$h->activo ? 'text-muted' : ''); ?>">
                                        <td><?php echo e($h->especialidad->nombre ?? '—'); ?></td>
                                        <td><?php echo e($h->maestro->nombre ?? '—'); ?></td>
                                        <td class="<?php echo e(!$h->activo ? 'text-decoration-line-through' : ''); ?>"><?php echo e($h->diaLabel()); ?></td>
                                        <td class="<?php echo e(!$h->activo ? 'text-decoration-line-through' : ''); ?>"><?php echo e(\Carbon\Carbon::parse($h->hora_inicio)->format('H:i')); ?></td>
                                        <td><?php echo e($h->salon ?? '—'); ?></td>
                                        <td><?php if(!$h->activo): ?><span class="badge bg-light text-muted border">dado de baja</span><?php endif; ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted small mb-0">Este alumno aun no tiene periodos registrados.</p>
            <?php endif; ?>
        </div>

        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-cash-coin me-1"></i> Últimos pagos</h6>
            <?php if($pagos->isEmpty()): ?>
                <p class="text-muted small mb-0">No hay pagos registrados. (El detalle de montos se gestiona en el módulo de Pagos.)</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Periodo</th><th>Concepto</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $pagos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($p->mesLabel()); ?> <?php echo e($p->anio); ?></td>
                                <td><?php echo e($p->concepto ?? 'Mensualidad'); ?></td>
                                <td>
                                    <span class="badge <?php echo e(['pendiente'=>'bg-danger','a_cuenta'=>'bg-warning text-dark','pagado'=>'bg-success'][$p->estado] ?? 'bg-secondary'); ?>">
                                        <?php echo e($p->estadoLabel()); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <a href="<?php echo e(route('pagos.index')); ?>" class="small d-inline-block mt-2">Ver montos y abonos en Pagos &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function editarAlumnoDesdeShow(id) {
        window.location.href = "<?php echo e(route('alumnos.index')); ?>?editar=" + id;
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/alumnos/show.blade.php ENDPATH**/ ?>