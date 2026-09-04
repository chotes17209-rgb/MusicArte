<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Nombre</th><th>Edad</th><th>Talleres</th><th>Maestro(s)</th><th>Tutor</th><th>Celular</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $alumnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="fw-semibold"><?php echo e($a->nombre); ?></td>
                <td><?php echo e($a->edad !== null ? $a->edad.' años' : '—'); ?></td>
                <td><?php echo e($a->talleresLabel()); ?></td>
                <td><?php echo e($a->maestrosLabel()); ?></td>
                <td><?php echo e($a->tutor ?? '—'); ?></td>
                <td><?php echo e($a->celular ?? '—'); ?></td>
                <td><?php if($a->activo): ?><span class="badge bg-success">Activo</span><?php else: ?><span class="badge bg-secondary">Inactivo</span><?php endif; ?></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-light btn-icon" onclick="editarAlumno(<?php echo e($a->id); ?>)"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarAlumno(<?php echo e($a->id); ?>, '<?php echo e($a->nombre); ?>')"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron alumnos con los filtros aplicados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="mt-2"><?php echo e($alumnos->links()); ?></div>
<?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/alumnos/_tabla.blade.php ENDPATH**/ ?>