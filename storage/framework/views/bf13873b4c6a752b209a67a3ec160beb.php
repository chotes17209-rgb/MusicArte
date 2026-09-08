
<?php $__env->startSection('titulo', 'Historial de alumnos'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Historial de actividad por periodo</h5>
        <small class="text-muted">En que meses estuvo activo o inactivo cada alumno</small>
    </div>
    <a href="<?php echo e(route('alumnos.index')); ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Alumnos
    </a>
</div>

<div class="card p-3">
    <input type="text" id="filtroHistorial" class="form-control mb-3" placeholder="Buscar alumno por nombre..." autocomplete="off">

    <?php if($periodos->isEmpty()): ?>
        <div class="text-center text-muted py-5">Aun no hay periodos creados. Ve al modulo "Periodos" para crear el primero.</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle table-sm" id="tablaHistorial">
            <thead class="table-light">
                <tr>
                    <th style="min-width:180px">Alumno</th>
                    <?php $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="text-center" style="min-width:110px"><?php echo e($p->nombre); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $alumnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="fila-historial" data-nombre="<?php echo e(Str::lower($a->nombre)); ?>">
                    <td class="fw-semibold"><?php echo e($a->nombre); ?></td>
                    <?php $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $registro = $registros[$a->id][$p->id] ?? null; ?>
                        <td class="text-center">
                            <?php if(!$registro): ?>
                                <span class="text-muted">—</span>
                            <?php elseif($registro->estado === 'activo'): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="<?php echo e($periodos->count() + 1); ?>" class="text-center text-muted py-4">No hay alumnos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <small class="text-muted">"—" significa que el alumno no tenia ningun taller registrado ese periodo (nunca estuvo matriculado ese mes).</small>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // 1.3 Busqueda reactiva: como toda la tabla ya esta cargada, filtramos
    // las filas en el navegador sin ir al servidor.
    document.getElementById('filtroHistorial')?.addEventListener('input', function () {
        const texto = this.value.trim().toLowerCase();
        document.querySelectorAll('#tablaHistorial .fila-historial').forEach(fila => {
            fila.style.display = fila.dataset.nombre.includes(texto) ? '' : 'none';
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/alumnos/historial.blade.php ENDPATH**/ ?>