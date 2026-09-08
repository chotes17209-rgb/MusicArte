<?php $__env->startSection('titulo', 'Confirmar acceso'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-center">
    <div class="card p-4" style="max-width: 420px; width: 100%;">
        <div class="text-center mb-3">
            <i class="bi bi-shield-lock fs-1" style="color:#3d2c8d"></i>
            <h5 class="fw-semibold mt-2 mb-1">Zona protegida</h5>
            <small class="text-muted">El modulo de Pagos contiene informacion sensible. Confirma tu contrasena para continuar.</small>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger py-2"><?php echo e($errors->first('password')); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('pagos.confirmar.submit')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Contrasena</label>
                <input type="password" name="password" class="form-control" autofocus required>
            </div>
            <button type="submit" class="btn btn-morado w-100">Confirmar y continuar</button>
            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light w-100 mt-2">Cancelar</a>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/auth/confirmar-pagos.blade.php ENDPATH**/ ?>