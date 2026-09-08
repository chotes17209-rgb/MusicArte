
<?php $__env->startSection('titulo', 'Periodos'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Periodos</h5>
        <small class="text-muted">Define cuanto dura cada periodo de clases (normalmente 4 semanas por mes)</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalPaseAlumnos" onclick="prepararPaseAlumnos()">
            <i class="bi bi-arrow-right-circle me-1"></i> Pasar alumnos al siguiente periodo
        </button>
        <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalPeriodo" onclick="nuevoPeriodo()">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Periodo
        </button>
    </div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Periodo</th><th>Duracion</th><th>Desde</th><th>Hasta</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold"><?php echo e($p->nombre); ?></td>
                    <td><?php echo e($p->duracionSemanas()); ?> semanas</td>
                    <td><?php echo e($p->fecha_inicio->format('d/m/Y')); ?></td>
                    <td><?php echo e($p->fecha_fin->format('d/m/Y')); ?></td>
                    <td><?php if($p->activo): ?><span class="badge bg-success">Activo</span><?php else: ?><span class="badge bg-secondary">Inactivo</span><?php endif; ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarPeriodo(<?php echo e($p->id); ?>)"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarPeriodo(<?php echo e($p->id); ?>, '<?php echo e($p->nombre); ?>')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aun no hay periodos creados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="text-end">
        <a href="<?php echo e(route('alumnos.historial')); ?>" class="small">Ver historial de actividad por alumno <i class="bi bi-arrow-right"></i></a>
    </div>
</div>

<div class="modal fade" id="modalPeriodo" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formPeriodo">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalPeriodo">Nuevo Periodo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="periodo_id">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Mes</label>
                        <select class="form-select" id="periodo_mes" required>
                            <option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option>
                            <option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option>
                            <option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option>
                            <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Ano</label>
                        <input type="number" class="form-control" id="periodo_anio" value="<?php echo e(date('Y')); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha inicio</label>
                        <input type="date" class="form-control" id="periodo_fecha_inicio">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha fin</label>
                        <input type="date" class="form-control" id="periodo_fecha_fin">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-light mb-3" onclick="calcularRangoAutomatico()">
                    <i class="bi bi-magic me-1"></i> Calcular 4 semanas automaticamente
                </button>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="periodo_activo" checked>
                    <label class="form-check-label small">Periodo activo</label>
                </div>
                <div class="alert alert-secondary small mt-3 mb-0">Si dejas las fechas vacias, se calculan automaticamente 4 semanas desde el dia 1 del mes.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="modalPaseAlumnos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title">Pasar alumnos al siguiente periodo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Periodo anterior</label>
                        <select class="form-select" id="pase_periodo_anterior_id" onchange="cargarCandidatosPase()">
                            <option value="">-- Selecciona --</option>
                            <?php $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Periodo nuevo (destino)</label>
                        <select class="form-select" id="pase_periodo_nuevo_id">
                            <option value="">-- Selecciona --</option>
                            <?php $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div id="pase_estado_vacio" class="text-muted small">Selecciona el periodo anterior para ver los alumnos que estuvieron activos.</div>

                <div id="pase_lista_wrap" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Alumnos activos en el periodo anterior. Desmarca a quienes no continuan.</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-light" onclick="marcarTodosPase(true)">Marcar todos</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="marcarTodosPase(false)">Desmarcar todos</button>
                        </div>
                    </div>
                    <div id="pase_lista_alumnos" class="border rounded p-2" style="max-height:320px; overflow-y:auto;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-morado" onclick="confirmarPaseAlumnos()">
                    <i class="bi bi-arrow-right-circle me-1"></i> Pasar seleccionados al nuevo periodo
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const modalPeriodo = new bootstrap.Modal('#modalPeriodo');

    function nuevoPeriodo() {
        document.getElementById('formPeriodo').reset();
        document.getElementById('periodo_id').value = '';
        document.getElementById('periodo_anio').value = new Date().getFullYear();
        document.getElementById('tituloModalPeriodo').innerText = 'Nuevo Periodo';
    }

    function calcularRangoAutomatico() {
        const mes = parseInt(document.getElementById('periodo_mes').value);
        const anio = parseInt(document.getElementById('periodo_anio').value);
        const inicio = new Date(anio, mes - 1, 1);
        const fin = new Date(anio, mes - 1, 1 + 27);
        const fmt = (d) => d.toISOString().substring(0, 10);
        document.getElementById('periodo_fecha_inicio').value = fmt(inicio);
        document.getElementById('periodo_fecha_fin').value = fmt(fin);
    }

    async function editarPeriodo(id) {
        const res = await maFetch(`/periodos/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('periodo_id').value = d.id;
        document.getElementById('periodo_mes').value = d.mes;
        document.getElementById('periodo_anio').value = d.anio;
        document.getElementById('periodo_fecha_inicio').value = d.fecha_inicio;
        document.getElementById('periodo_fecha_fin').value = d.fecha_fin;
        document.getElementById('periodo_activo').checked = !!d.activo;
        document.getElementById('tituloModalPeriodo').innerText = 'Editar Periodo';
        modalPeriodo.show();
    }

    document.getElementById('formPeriodo').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('periodo_id').value;
        const payload = {
            mes: document.getElementById('periodo_mes').value,
            anio: document.getElementById('periodo_anio').value,
            fecha_inicio: document.getElementById('periodo_fecha_inicio').value || null,
            fecha_fin: document.getElementById('periodo_fecha_fin').value || null,
            activo: document.getElementById('periodo_activo').checked ? 1 : 0,
        };
        const url = id ? `/periodos/${id}` : '/periodos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalPeriodo.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarPeriodo(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/periodos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }

    /* ---------------------------------------------------------------
     * Seccion 8: pasar alumnos activos al siguiente periodo.
     * ------------------------------------------------------------- */
    let candidatosPaseActual = [];

    function prepararPaseAlumnos() {
        document.getElementById('pase_periodo_anterior_id').value = '';
        document.getElementById('pase_periodo_nuevo_id').value = '';
        document.getElementById('pase_lista_wrap').classList.add('d-none');
        document.getElementById('pase_estado_vacio').classList.remove('d-none');
        document.getElementById('pase_lista_alumnos').innerHTML = '';
        candidatosPaseActual = [];
    }

    async function cargarCandidatosPase() {
        const periodoId = document.getElementById('pase_periodo_anterior_id').value;
        if (!periodoId) {
            document.getElementById('pase_lista_wrap').classList.add('d-none');
            document.getElementById('pase_estado_vacio').classList.remove('d-none');
            return;
        }

        const res = await maFetch(`/periodos/${periodoId}/candidatos`);
        if (!res || !res.ok) return;

        candidatosPaseActual = res.data;
        const cont = document.getElementById('pase_lista_alumnos');

        if (!candidatosPaseActual.length) {
            document.getElementById('pase_lista_wrap').classList.add('d-none');
            document.getElementById('pase_estado_vacio').classList.remove('d-none');
            document.getElementById('pase_estado_vacio').innerText = 'Ese periodo no tiene alumnos activos registrados.';
            return;
        }

        document.getElementById('pase_estado_vacio').classList.add('d-none');
        document.getElementById('pase_lista_wrap').classList.remove('d-none');

        cont.innerHTML = candidatosPaseActual.map(a => `
            <div class="form-check">
                <input class="form-check-input pase-alumno-checkbox" type="checkbox" value="${a.id}" id="pase_alumno_${a.id}" checked>
                <label class="form-check-label" for="pase_alumno_${a.id}">${a.nombre}</label>
            </div>
        `).join('');
    }

    function marcarTodosPase(marcar) {
        document.querySelectorAll('.pase-alumno-checkbox').forEach(cb => cb.checked = marcar);
    }

    async function confirmarPaseAlumnos() {
        const periodoAnteriorId = document.getElementById('pase_periodo_anterior_id').value;
        const periodoNuevoId = document.getElementById('pase_periodo_nuevo_id').value;

        if (!periodoAnteriorId || !periodoNuevoId) {
            Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Selecciona el periodo anterior y el periodo nuevo.' });
            return;
        }
        if (periodoAnteriorId === periodoNuevoId) {
            Swal.fire({ icon: 'warning', title: 'Periodos iguales', text: 'El periodo anterior y el nuevo deben ser distintos.' });
            return;
        }

        const seleccionados = Array.from(document.querySelectorAll('.pase-alumno-checkbox:checked')).map(cb => cb.value);

        const confirmacion = await Swal.fire({
            icon: 'question',
            title: 'Confirmar pase de alumnos',
            text: `${seleccionados.length} alumno(s) pasaran activos al nuevo periodo. Los no marcados quedaran inactivos ese mes. ¿Continuar?`,
            showCancelButton: true,
            confirmButtonText: 'Si, pasar alumnos',
            cancelButtonText: 'Cancelar',
        });
        if (!confirmacion.isConfirmed) return;

        const res = await maFetch(`/periodos/${periodoNuevoId}/pasar-alumnos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ periodo_anterior_id: periodoAnteriorId, alumno_ids: seleccionados }),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            bootstrap.Modal.getInstance(document.getElementById('modalPaseAlumnos')).hide();
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/periodos/index.blade.php ENDPATH**/ ?>