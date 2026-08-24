<?php $__env->startSection('titulo', 'Asistencia'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Control de Asistencia</h5>
        <small class="text-muted">Marca la asistencia de cada clase del dia</small>
    </div>
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="fecha" value="<?php echo e($fecha); ?>" class="form-control" onchange="this.form.submit()">
    </form>
</div>

<div class="card p-3 mb-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Hora</th><th>Alumno</th><th>Maestro</th><th>Especialidad</th><th>Asistencia</th><th class="text-end">Accion</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $clases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e(\Carbon\Carbon::parse($c->hora_inicio)->format('H:i')); ?></td>
                    <td class="fw-semibold"><?php echo e($c->alumno->nombre); ?></td>
                    <td><?php echo e($c->maestro->nombre ?? '—'); ?></td>
                    <td><?php echo e($c->especialidad->nombre ?? '—'); ?></td>
                    <td>
                        <span id="badge-asistencia-<?php echo e($c->id); ?>"
                            class="badge <?php echo e($c->asistencia ? ['asistio'=>'bg-success','falto'=>'bg-danger','justificado'=>'bg-warning text-dark','tardanza'=>'bg-info text-dark'][$c->asistencia->estado] : 'bg-secondary'); ?>"
                            style="cursor:pointer"
                            title="Clic para marcar como Asistio"
                            onclick="marcarRapido(<?php echo e($c->id); ?>)">
                            <?php echo e($c->asistencia ? ucfirst($c->asistencia->estado) : 'Sin marcar'); ?>

                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-morado" onclick="marcarAsistencia(<?php echo e($c->id); ?>, '<?php echo e($c->alumno->nombre); ?>', '<?php echo e($c->asistencia->estado ?? ''); ?>')">
                            <i class="bi bi-clipboard-check"></i> Marcar
                        </button>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No hay clases programadas para esta fecha.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card p-3">
    <h6 class="fw-semibold mb-3"><i class="bi bi-graph-up me-1"></i> Resumen de asistencia por alumno</h6>
    <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="fecha" value="<?php echo e($fecha); ?>">
        <div class="col-md-5">
            <select name="alumno_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Selecciona un alumno --</option>
                <?php $__currentLoopData = $alumnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($a->id); ?>" <?php if(request('alumno_id')==$a->id): echo 'selected'; endif; ?>><?php echo e($a->nombre); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </form>

    <?php if($resumenAlumno): ?>
        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Total clases</div><div class="fs-4 fw-bold"><?php echo e($resumenAlumno['total']); ?></div></div></div>
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Asistio</div><div class="fs-4 fw-bold text-success"><?php echo e($resumenAlumno['asistio']); ?></div></div></div>
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">% Asistencia</div><div class="fs-4 fw-bold" style="color:#3d2c8d"><?php echo e($resumenAlumno['porcentaje']); ?>%</div></div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Fecha</th><th>Estado</th><th>Observacion</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $resumenAlumno['detalle']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(optional($d->clase)->fecha?->format('d/m/Y')); ?></td>
                        <td><?php echo e(ucfirst($d->estado)); ?></td>
                        <td><?php echo e($d->observacion ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL MARCAR ASISTENCIA -->
<div class="modal fade" id="modalAsistencia" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formAsistencia">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalAsistencia">Marcar Asistencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="asistencia_clase_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Estado</label>
                    <select class="form-select" id="asistencia_estado" required>
                        <option value="asistio">Asistio</option>
                        <option value="falto">Falto</option>
                        <option value="justificado">Justificado</option>
                        <option value="tardanza">Tardanza</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Observacion (opcional)</label>
                    <textarea class="form-control" id="asistencia_observacion" rows="2"></textarea>
                </div>
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
    const modalAsistencia = new bootstrap.Modal('#modalAsistencia');

    const BADGE_CLASES = {
        asistio: 'bg-success',
        falto: 'bg-danger',
        justificado: 'bg-warning text-dark',
        tardanza: 'bg-info text-dark',
    };

    function pintarBadge(claseId, estado) {
        const badge = document.getElementById(`badge-asistencia-${claseId}`);
        if (!badge) return;
        badge.className = 'badge ' + (BADGE_CLASES[estado] || 'bg-secondary');
        badge.textContent = estado ? estado.charAt(0).toUpperCase() + estado.slice(1) : 'Sin marcar';
    }

    // Clic rapido en la etiqueta: marca "Asistio" al instante, sin abrir modal.
    async function marcarRapido(claseId) {
        const res = await maFetch(`/asistencia/${claseId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado: 'asistio' }),
        });
        if (res && res.ok) {
            pintarBadge(claseId, 'asistio');
            maToast('success', 'Marcado como Asistio');
        }
    }

    // Boton "Marcar": abre el modal para elegir otro estado y agregar observacion.
    function marcarAsistencia(claseId, alumnoNombre, estadoActual) {
        document.getElementById('asistencia_clase_id').value = claseId;
        document.getElementById('asistencia_estado').value = estadoActual || 'asistio';
        document.getElementById('asistencia_observacion').value = '';
        document.getElementById('tituloModalAsistencia').innerText = `Asistencia — ${alumnoNombre}`;
        modalAsistencia.show();
    }

    document.getElementById('formAsistencia').addEventListener('submit', async (e) => {
        e.preventDefault();
        const claseId = document.getElementById('asistencia_clase_id').value;
        const payload = {
            estado: document.getElementById('asistencia_estado').value,
            observacion: document.getElementById('asistencia_observacion').value,
        };
        const res = await maFetch(`/asistencia/${claseId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            pintarBadge(claseId, payload.estado);
            maToast('success', res.message);
            modalAsistencia.hide();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/asistencia/index.blade.php ENDPATH**/ ?>