
<?php $__env->startSection('titulo', 'Alumnos'); ?>

<?php $__env->startSection('contenido'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Alumnos</h5>
        <small class="text-muted">Registro de estudiantes del centro cultural</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="nuevoAlumno()">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Alumno
    </button>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET" id="formFiltros">
        <div class="col-md-3">
            <input type="text" name="buscar" id="filtro_buscar" class="form-control" placeholder="Buscar por nombre, DNI o tutor..." value="<?php echo e(request('buscar')); ?>">
        </div>
        <div class="col-md-2">
            <select name="periodo_id" id="filtro_periodo_id" class="form-select">
                <option value="">Todos los periodos</option>
                <?php $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($p->id); ?>" <?php if(request('periodo_id') == $p->id): echo 'selected'; endif; ?>><?php echo e($p->nombre); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="maestro_id" id="filtro_maestro_id" class="form-select">
                <option value="">Todos los maestros</option>
                <?php $__currentLoopData = $maestros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($m->id); ?>" <?php if(request('maestro_id') == $m->id): echo 'selected'; endif; ?>><?php echo e($m->nombre); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="especialidad_id" id="filtro_especialidad_id" class="form-select">
                <option value="">Todas las especialidades</option>
                <?php $__currentLoopData = $especialidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $esp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($esp->id); ?>" <?php if(request('especialidad_id') == $esp->id): echo 'selected'; endif; ?>><?php echo e($esp->nombre); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" id="filtro_estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="activo" <?php if(request('estado')=='activo'): echo 'selected'; endif; ?>>Activos</option>
                <option value="inactivo" <?php if(request('estado')=='inactivo'): echo 'selected'; endif; ?>>Inactivos</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-light w-100" onclick="window.print()" title="Imprimir lista filtrada"><i class="bi bi-printer"></i></button>
        </div>
    </form>
</div>

<div class="card p-3">
    <div id="contenedorTablaAlumnos">
        <?php echo $__env->make('alumnos._tabla', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>

<div class="modal fade" id="modalAlumno" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form class="modal-content" id="formAlumno">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalAlumno">Nuevo Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="alumno_id">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label small fw-semibold">Nombre completo</label>
                        <input type="text" class="form-control" id="alumno_nombre" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Edad (calculada)</label>
                        <input type="text" class="form-control" id="alumno_edad_preview" disabled placeholder="Se calcula sola">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha de nacimiento</label>
                        <input type="date" class="form-control" id="alumno_fecha_nacimiento">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">DNI</label>
                        <input type="text" class="form-control" id="alumno_dni">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Tutor / Apoderado</label>
                        <input type="text" class="form-control" id="alumno_tutor">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Celular de contacto</label>
                        <input type="text" class="form-control" id="alumno_celular">
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold small text-uppercase text-muted mb-0">Talleres del alumno</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="agregarTaller()">
                        <i class="bi bi-plus-lg me-1"></i>Agregar taller
                    </button>
                </div>
                <small class="text-muted d-block mb-2">Un alumno puede estar inscrito en varios talleres a la vez. Cada uno tiene su propio maestro, periodo y horario.</small>
                <div id="contenedorTalleres"></div>

                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha de ingreso</label>
                        <input type="date" class="form-control" id="alumno_fecha_ingreso">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="alumno_activo" checked>
                            <label class="form-check-label small">Alumno activo</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Diagnostico / condicion especial (opcional)</label>
                    <textarea class="form-control" id="alumno_diagnostico" rows="2"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Observaciones</label>
                    <textarea class="form-control" id="alumno_observaciones" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>

<template id="plantillaTaller">
    <div class="card p-3 mb-3 taller-bloque" data-taller>
        <div class="d-flex justify-content-between align-items-start">
            <h6 class="small fw-semibold text-morado mb-2">Taller</h6>
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('[data-taller]').remove()">
                <i class="bi bi-x-circle"></i> Quitar
            </button>
        </div>
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label small fw-semibold">Especialidad</label>
                <select class="form-select form-select-sm taller-especialidad" required>
                    <option value="">-- Selecciona --</option>
                    <?php $__currentLoopData = $especialidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $esp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($esp->id); ?>"><?php echo e($esp->nombre); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small fw-semibold">Maestro</label>
                <select class="form-select form-select-sm taller-maestro">
                    <option value="">-- Selecciona --</option>
                    <?php $__currentLoopData = $maestros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>"><?php echo e($m->nombre); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small fw-semibold">Salon</label>
                <input type="text" class="form-control form-control-sm taller-salon">
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 mb-2">
                <label class="form-label small fw-semibold">Periodo</label>
                <select class="form-select form-select-sm taller-periodo" required>
                    <option value="">-- Selecciona --</option>
                    <?php $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>"><?php echo e($p->nombre); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th style="width:36px"></th><th>Dia</th><th>Hora inicio</th><th>Hora fin</th></tr></thead>
                <tbody>
                    <?php $__currentLoopData = ['1'=>'Lunes','2'=>'Martes','3'=>'Miercoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sabado','7'=>'Domingo']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><input class="form-check-input taller-dia" type="checkbox" value="<?php echo e($num); ?>" data-dia="<?php echo e($num); ?>"></td>
                        <td><?php echo e($label); ?></td>
                        <td><input type="time" class="form-control form-control-sm taller-hora-inicio" data-dia="<?php echo e($num); ?>" disabled></td>
                        <td><input type="time" class="form-control form-control-sm taller-hora-fin" data-dia="<?php echo e($num); ?>" disabled></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</template>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const modalAlumno = new bootstrap.Modal('#modalAlumno');

    // ---------- Busqueda / filtrado reactivo (req. 1.3) ----------
    let filtroDebounce = null;
    function recargarTablaAlumnos() {
        const form = document.getElementById('formFiltros');
        const params = new URLSearchParams(new FormData(form)).toString();
        history.replaceState(null, '', '<?php echo e(url('/alumnos')); ?>' + (params ? '?' + params : ''));
        fetch('<?php echo e(url('/alumnos')); ?>?' + params, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => { document.getElementById('contenedorTablaAlumnos').innerHTML = html; });
    }
    document.getElementById('filtro_buscar').addEventListener('input', () => {
        clearTimeout(filtroDebounce);
        filtroDebounce = setTimeout(recargarTablaAlumnos, 350);
    });
    ['filtro_periodo_id', 'filtro_maestro_id', 'filtro_especialidad_id', 'filtro_estado'].forEach(id => {
        document.getElementById(id).addEventListener('change', recargarTablaAlumnos);
    });
    document.getElementById('formFiltros').addEventListener('submit', e => e.preventDefault());

    // ---------- Talleres dinamicos dentro del modal ----------
    function agregarTaller(datos) {
        const tpl = document.getElementById('plantillaTaller').content.cloneNode(true);
        const bloque = tpl.querySelector('[data-taller]');

        bloque.querySelectorAll('.taller-dia').forEach(cb => {
            cb.addEventListener('change', function () {
                const dia = this.dataset.dia;
                const hi = bloque.querySelector(`.taller-hora-inicio[data-dia="${dia}"]`);
                const hf = bloque.querySelector(`.taller-hora-fin[data-dia="${dia}"]`);
                hi.disabled = !this.checked;
                hf.disabled = !this.checked;
                if (!this.checked) { hi.value = ''; hf.value = ''; }
            });
        });

        document.getElementById('contenedorTalleres').appendChild(bloque);

        if (datos) {
            const bloqueInsertado = document.getElementById('contenedorTalleres').lastElementChild;
            bloqueInsertado.querySelector('.taller-especialidad').value = datos.especialidad_id ?? '';
            bloqueInsertado.querySelector('.taller-maestro').value = datos.maestro_id ?? '';
            bloqueInsertado.querySelector('.taller-salon').value = datos.salon ?? '';
            bloqueInsertado.querySelector('.taller-periodo').value = datos.periodo_id ?? '';
            (datos.horarios || []).forEach(h => {
                const cb = bloqueInsertado.querySelector(`.taller-dia[data-dia="${h.dia_semana}"]`);
                const hi = bloqueInsertado.querySelector(`.taller-hora-inicio[data-dia="${h.dia_semana}"]`);
                const hf = bloqueInsertado.querySelector(`.taller-hora-fin[data-dia="${h.dia_semana}"]`);
                if (cb) { cb.checked = true; }
                if (hi) { hi.disabled = false; hi.value = (h.hora_inicio || '').substring(0, 5); }
                if (hf) { hf.disabled = false; hf.value = (h.hora_fin || '').substring(0, 5); }
            });
        }
    }

    function recalcularEdadPreview() {
        const fn = document.getElementById('alumno_fecha_nacimiento').value;
        const out = document.getElementById('alumno_edad_preview');
        if (!fn) { out.value = ''; return; }
        const nac = new Date(fn);
        const hoy = new Date();
        let edad = hoy.getFullYear() - nac.getFullYear();
        const noHaCumplido = (hoy.getMonth() < nac.getMonth()) || (hoy.getMonth() === nac.getMonth() && hoy.getDate() < nac.getDate());
        if (noHaCumplido) edad--;
        out.value = edad + ' años';
    }
    document.getElementById('alumno_fecha_nacimiento').addEventListener('change', recalcularEdadPreview);

    function nuevoAlumno() {
        document.getElementById('formAlumno').reset();
        document.getElementById('alumno_id').value = '';
        document.getElementById('alumno_edad_preview').value = '';
        document.getElementById('contenedorTalleres').innerHTML = '';
        agregarTaller();
        document.getElementById('tituloModalAlumno').innerText = 'Nuevo Alumno';
    }

    async function editarAlumno(id) {
        const res = await maFetch(`/alumnos/${id}/edit`);
        if (!res) return;
        const d = res.data;

        document.getElementById('alumno_id').value = d.id;
        document.getElementById('alumno_nombre').value = d.nombre;
        document.getElementById('alumno_fecha_nacimiento').value = d.fecha_nacimiento ?? '';
        recalcularEdadPreview();
        document.getElementById('alumno_dni').value = d.dni ?? '';
        document.getElementById('alumno_tutor').value = d.tutor ?? '';
        document.getElementById('alumno_celular').value = d.celular ?? '';
        document.getElementById('alumno_fecha_ingreso').value = d.fecha_ingreso ?? '';
        document.getElementById('alumno_activo').checked = !!d.activo;
        document.getElementById('alumno_diagnostico').value = d.diagnostico ?? '';
        document.getElementById('alumno_observaciones').value = d.observaciones ?? '';

        document.getElementById('contenedorTalleres').innerHTML = '';
        try {
            const talleres = d.talleres || [];
            if (talleres.length) {
                talleres.forEach(t => {
                    agregarTaller({
                        especialidad_id: t.especialidad ? t.especialidad.id : null,
                        maestro_id: t.maestro ? t.maestro.id : null,
                        salon: (t.horarios && t.horarios[0]) ? t.horarios[0].salon : null,
                        periodo_id: t.periodo ? t.periodo.id : null,
                        horarios: t.horarios,
                    });
                });
            } else {
                agregarTaller();
            }
        } catch (err) {
            console.error('No se pudo precargar los talleres del alumno:', err);
            agregarTaller();
        }

        document.getElementById('tituloModalAlumno').innerText = 'Editar Alumno';
        modalAlumno.show();
    }

    document.getElementById('formAlumno').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('alumno_id').value;

        const talleres = [];
        document.querySelectorAll('#contenedorTalleres [data-taller]').forEach(bloque => {
            const especialidad_id = bloque.querySelector('.taller-especialidad').value || null;
            const maestro_id = bloque.querySelector('.taller-maestro').value || null;
            const salon = bloque.querySelector('.taller-salon').value || null;
            const periodo_id = bloque.querySelector('.taller-periodo').value || null;
            const horarios = [];
            bloque.querySelectorAll('.taller-dia').forEach(cb => {
                if (cb.checked) {
                    const dia = cb.dataset.dia;
                    horarios.push({
                        dia_semana: dia,
                        hora_inicio: bloque.querySelector(`.taller-hora-inicio[data-dia="${dia}"]`).value,
                        hora_fin: bloque.querySelector(`.taller-hora-fin[data-dia="${dia}"]`).value,
                    });
                }
            });
            if (especialidad_id || horarios.length) {
                talleres.push({ especialidad_id, maestro_id, salon, periodo_id, horarios });
            }
        });

        for (const t of talleres) {
            if (!t.especialidad_id || !t.periodo_id) {
                Swal.fire({ icon: 'warning', title: 'Taller incompleto', text: 'Cada taller necesita especialidad y periodo.' });
                return;
            }
            if (!t.horarios.length) {
                Swal.fire({ icon: 'warning', title: 'Falta el horario', text: 'Marca al menos un dia de clase en cada taller.' });
                return;
            }
            if (t.horarios.some(h => !h.hora_inicio || !h.hora_fin)) {
                Swal.fire({ icon: 'warning', title: 'Horario incompleto', text: 'Completa la hora de inicio y fin en cada dia marcado.' });
                return;
            }
        }

        const payload = {
            nombre: document.getElementById('alumno_nombre').value,
            fecha_nacimiento: document.getElementById('alumno_fecha_nacimiento').value || null,
            dni: document.getElementById('alumno_dni').value,
            tutor: document.getElementById('alumno_tutor').value,
            celular: document.getElementById('alumno_celular').value,
            fecha_ingreso: document.getElementById('alumno_fecha_ingreso').value || null,
            activo: document.getElementById('alumno_activo').checked ? 1 : 0,
            diagnostico: document.getElementById('alumno_diagnostico').value,
            observaciones: document.getElementById('alumno_observaciones').value,
        };

        if (talleres.length) {
            payload.talleres = talleres;
        }

        const url = id ? `/alumnos/${id}` : '/alumnos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalAlumno.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarAlumno(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/alumnos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/alumnos/index.blade.php ENDPATH**/ ?>