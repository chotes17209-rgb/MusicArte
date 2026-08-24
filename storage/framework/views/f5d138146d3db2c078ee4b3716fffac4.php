<?php $__env->startSection('titulo', 'Horarios - Vista Mensual'); ?>

<?php $__env->startPush('estilos'); ?>
<style>
    .dia-badge { display:inline-block; min-width:26px; padding:2px 5px; margin:1px; border-radius:5px; font-size:.72rem; font-weight:600; text-align:center; }
    .dia-realizada { background:#c8e6c9; color:#1b5e20; }
    .dia-programada { background:#fff3cd; color:#7a5b00; }
    .dia-cancelada { background:#f8d7da; color:#842029; }
    .dia-proyectada { background:#e9ecef; color:#495057; }
    .semana-col { min-width:140px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="<?php echo e(route('horarios.index')); ?>" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Horarios</a>
        <h5 class="fw-semibold mb-0 mt-1">Horarios por Alumno — <?php echo e(\App\Models\Pago::MESES[$mes]); ?> <?php echo e($anio); ?></h5>
        <small class="text-muted">Cada mes dividido en 4 semanas, igual que tu cuadro de control</small>
    </div>
    <form class="d-flex gap-2" method="GET">
        <select name="mes" class="form-select form-select-sm">
            <?php $__currentLoopData = \App\Models\Pago::MESES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $nombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($num); ?>" <?php if($mes==$num): echo 'selected'; endif; ?>><?php echo e($nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input type="number" name="anio" value="<?php echo e($anio); ?>" class="form-control form-control-sm" style="width:90px">
        <button class="btn btn-sm btn-light">Filtrar</button>
    </form>
</div>

<div class="d-flex gap-3 small mb-3">
    <span><span class="dia-badge dia-realizada">15</span> Realizada</span>
    <span><span class="dia-badge dia-programada">15</span> Programada</span>
    <span><span class="dia-badge dia-cancelada">15</span> Cancelada</span>
    <span><span class="dia-badge dia-proyectada">15</span> Proyectada (aun no generada en el calendario)</span>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Alumno</th><th>Especialidad</th><th>Maestro</th><th>Horario</th>
                    <th class="semana-col">Semana 1<br><small class="text-muted">(1-7)</small></th>
                    <th class="semana-col">Semana 2<br><small class="text-muted">(8-14)</small></th>
                    <th class="semana-col">Semana 3<br><small class="text-muted">(15-21)</small></th>
                    <th class="semana-col">Semana 4<br><small class="text-muted">(22-fin)</small></th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $filas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fila): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($fila['alumno']->nombre ?? '—'); ?></td>
                    <td><?php echo e($fila['especialidad']->nombre ?? '—'); ?></td>
                    <td><?php echo e($fila['maestro']->nombre ?? '—'); ?></td>
                    <td class="small"><?php echo e($fila['horario_texto']); ?></td>
                    <?php $__currentLoopData = [1,2,3,4]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td>
                            <?php $__empty_2 = true; $__currentLoopData = $fila['semanas'][$s]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <span class="dia-badge dia-<?php echo e($d['estado']); ?>"><?php echo e($d['dia']); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No hay horarios activos configurados. Ve a "Nuevo Horario" para agregar alumnos.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/horarios/mensual.blade.php ENDPATH**/ ?>