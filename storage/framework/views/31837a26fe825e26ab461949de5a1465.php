<?php $__env->startSection('titulo', 'Horarios'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Horarios (plantilla semanal)</h5>
        <small class="text-muted">Define el dia y hora fija de cada alumno; luego genera las clases en el calendario</small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('horarios.mensual')); ?>" class="btn btn-outline-secondary"><i class="bi bi-calendar-week me-1"></i> Vista mensual por semanas</a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalGenerar"><i class="bi bi-calendar-plus me-1"></i> Generar clases</button>
        <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalHorario" onclick="nuevoHorario()"><i class="bi bi-plus-lg me-1"></i> Nuevo Horario</button>
    </div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Dia</th><th>Hora</th><th>Maestro</th><th>Especialidad</th><th>Salon</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $horarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($h->alumno->nombre); ?></td>
                    <td><?php echo e($h->diaLabel()); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($h->hora_inicio)->format('H:i')); ?> - <?php echo e(\Carbon\Carbon::parse($h->hora_fin)->format('H:i')); ?></td>
                    <td><?php echo e($h->maestro->nombre ?? '—'); ?></td>
                    <td><?php echo e($h->especialidad->nombre ?? '—'); ?></td>
                    <td><?php echo e($h->salon ?? '—'); ?></td>
                    <td><?php if($h->activo): ?><span class="badge bg-success">Activo</span><?php else: ?><span class="badge bg-secondary">Inactivo</span><?php endif; ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarHorario(<?php echo e($h->id); ?>)"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarHorario(<?php echo e($h->id); ?>, '<?php echo e($h->alumno->nombre); ?>')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Aun no hay horarios registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL HORARIO -->
<div class="modal fade" id="modalHorario" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formHorario">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalHorario">Nuevo Horario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="horario_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alumno</label>
                    <select class="form-select" id="horario_alumno_id" required>
                        <option value="">-- Selecciona --</option>
                        <?php $__currentLoopData = $alumnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($a->id); ?>"><?php echo e($a->nombre); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Maestro</label>
                    <select class="form-select" id="horario_maestro_id">
                        <option value="">-- Selecciona --</option>
                        <?php $__currentLoopData = $maestros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"><?php echo e($m->nombre); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Dia de la semana</label>
                    <select class="form-select" id="horario_dia_semana" required>
                        <option value="1">Lunes</option><option value="2">Martes</option><option value="3">Miercoles</option>
                        <option value="4">Jueves</option><option value="5">Viernes</option><option value="6">Sabado</option><option value="7">Domingo</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Hora inicio</label>
                        <input type="time" class="form-control" id="horario_hora_inicio" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Hora fin</label>
                        <input type="time" class="form-control" id="horario_hora_fin" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Salon / Ambiente</label>
                    <input type="text" class="form-control" id="horario_salon">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="horario_activo" checked>
                    <label class="form-check-label small">Horario activo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL GENERAR CLASES -->
<div class="modal fade" id="modalGenerar" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formGenerar">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title">Generar clases en el calendario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Se crearan las clases del calendario a partir de todos los horarios activos, en el rango de fechas indicado.</p>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" class="form-control" id="generar_desde" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" class="form-control" id="generar_hasta" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Generar</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const modalHorario = new bootstrap.Modal('#modalHorario');
    const modalGenerar = new bootstrap.Modal('#modalGenerar');

    function nuevoHorario() {
        document.getElementById('formHorario').reset();
        document.getElementById('horario_id').value = '';
        document.getElementById('tituloModalHorario').innerText = 'Nuevo Horario';
    }

    async function editarHorario(id) {
        const res = await maFetch(`/horarios/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('horario_id').value = d.id;
        document.getElementById('horario_alumno_id').value = d.alumno_id;
        document.getElementById('horario_maestro_id').value = d.maestro_id ?? '';
        document.getElementById('horario_dia_semana').value = d.dia_semana;
        document.getElementById('horario_hora_inicio').value = d.hora_inicio;
        document.getElementById('horario_hora_fin').value = d.hora_fin;
        document.getElementById('horario_salon').value = d.salon ?? '';
        document.getElementById('horario_activo').checked = !!d.activo;
        document.getElementById('tituloModalHorario').innerText = 'Editar Horario';
        modalHorario.show();
    }

    document.getElementById('formHorario').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('horario_id').value;
        const payload = {
            alumno_id: document.getElementById('horario_alumno_id').value,
            maestro_id: document.getElementById('horario_maestro_id').value || null,
            dia_semana: document.getElementById('horario_dia_semana').value,
            hora_inicio: document.getElementById('horario_hora_inicio').value,
            hora_fin: document.getElementById('horario_hora_fin').value,
            salon: document.getElementById('horario_salon').value,
            activo: document.getElementById('horario_activo').checked ? 1 : 0,
        };
        const url = id ? `/horarios/${id}` : '/horarios';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalHorario.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarHorario(id, nombre) {
        if (!(await maConfirmarEliminar(`el horario de ${nombre}`))) return;
        const res = await maFetch(`/horarios/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }

    document.getElementById('formGenerar').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            desde: document.getElementById('generar_desde').value,
            hasta: document.getElementById('generar_hasta').value,
        };
        const res = await maFetch('/horarios-generar-clases', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalGenerar.hide();
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/horarios/index.blade.php ENDPATH**/ ?>