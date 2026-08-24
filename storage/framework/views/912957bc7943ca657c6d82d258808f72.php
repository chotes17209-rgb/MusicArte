<?php $__env->startSection('titulo', 'Especialidades'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-semibold mb-0">Catalogo de Especialidades</h5>
        <small class="text-muted">Instrumentos y disciplinas que ofrece el centro cultural</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalEspecialidad" onclick="nuevaEspecialidad()">
        <i class="bi bi-plus-lg me-1"></i> Nueva Especialidad
    </button>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Especialidad</th><th>Color</th>
                    <?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?><th>Precio mensual</th><?php endif; ?> <?php endif; ?>
                    <th>Alumnos</th><th>Maestros</th><th>Estado</th><th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $especialidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($e->nombre); ?></td>
                    <td><span class="d-inline-block rounded-circle" style="width:18px;height:18px;background:<?php echo e($e->color); ?>"></span></td>
                    <?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?><td>S/ <?php echo e(number_format($e->precio_mensual, 2)); ?></td><?php endif; ?> <?php endif; ?>
                    <td><?php echo e($e->alumnos_count); ?></td>
                    <td><?php echo e($e->maestros_count); ?></td>
                    <td><?php if($e->activo): ?><span class="badge bg-success">Activo</span><?php else: ?><span class="badge bg-secondary">Inactivo</span><?php endif; ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarEspecialidad(<?php echo e($e->id); ?>)"><i class="bi bi-pencil"></i></button>
                        <?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarEspecialidad(<?php echo e($e->id); ?>, '<?php echo e($e->nombre); ?>')"><i class="bi bi-trash"></i></button>
                        <?php endif; ?> <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Aun no hay especialidades registradas.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalEspecialidad" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formEspecialidad">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalEspecialidad">Nueva Especialidad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="especialidad_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nombre</label>
                    <input type="text" class="form-control" id="especialidad_nombre" required placeholder="Ej. Piano, Guitarra, Canto...">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Color en calendario</label>
                        <input type="color" class="form-control form-control-color w-100" id="especialidad_color" value="#3d2c8d">
                    </div>
                    <?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->esAdmin()): ?>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Precio mensual (S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="especialidad_precio" placeholder="0.00">
                    </div>
                    <?php endif; ?> <?php endif; ?>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="especialidad_activo" checked>
                    <label class="form-check-label small">Especialidad activa</label>
                </div>
                <?php if(auth()->guard()->check()): ?> <?php if(!auth()->user()->esAdmin()): ?>
                <div class="alert alert-secondary small mt-3 mb-0"><i class="bi bi-lock-fill me-1"></i>Solo el administrador puede modificar el precio.</div>
                <?php endif; ?> <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const modalEspecialidad = new bootstrap.Modal('#modalEspecialidad');

    function nuevaEspecialidad() {
        document.getElementById('formEspecialidad').reset();
        document.getElementById('especialidad_id').value = '';
        document.getElementById('especialidad_color').value = '#3d2c8d';
        document.getElementById('tituloModalEspecialidad').innerText = 'Nueva Especialidad';
    }

    async function editarEspecialidad(id) {
        const res = await maFetch(`/especialidades/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('especialidad_id').value = d.id;
        document.getElementById('especialidad_nombre').value = d.nombre;
        document.getElementById('especialidad_color').value = d.color;
        const precioInput = document.getElementById('especialidad_precio');
        if (precioInput) precioInput.value = d.precio_mensual;
        document.getElementById('especialidad_activo').checked = !!d.activo;
        document.getElementById('tituloModalEspecialidad').innerText = 'Editar Especialidad';
        modalEspecialidad.show();
    }

    document.getElementById('formEspecialidad').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('especialidad_id').value;
        const precioInput = document.getElementById('especialidad_precio');
        const payload = {
            nombre: document.getElementById('especialidad_nombre').value,
            color: document.getElementById('especialidad_color').value,
            precio_mensual: precioInput ? (precioInput.value || 0) : undefined,
            activo: document.getElementById('especialidad_activo').checked ? 1 : 0,
        };
        const url = id ? `/especialidades/${id}` : '/especialidades';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalEspecialidad.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarEspecialidad(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/especialidades/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/especialidades/index.blade.php ENDPATH**/ ?>