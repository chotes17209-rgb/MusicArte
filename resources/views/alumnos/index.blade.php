@extends('layouts.app')
@section('titulo', 'Alumnos')

@section('contenido')
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
    <form class="row g-2" method="GET">
        <div class="col-md-4">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..." value="{{ request('buscar') }}">
        </div>
        <div class="col-md-3">
            <select name="especialidad_id" class="form-select">
                <option value="">Todas las especialidades</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp->id }}" @selected(request('especialidad_id') == $esp->id)>{{ $esp->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="activo" @selected(request('estado')=='activo')>Activos</option>
                <option value="inactivo" @selected(request('estado')=='inactivo')>Inactivos</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-light w-100"><i class="bi bi-search me-1"></i>Filtrar</button>
        </div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nombre</th><th>Edad</th><th>Especialidad</th><th>Maestro</th><th>Tutor</th><th>Celular</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($alumnos as $a)
                <tr>
                    <td class="fw-semibold">{{ $a->nombre }}</td>
                    <td>{{ $a->edad ?? '—' }}</td>
                    <td>{{ $a->especialidad->nombre ?? '—' }}</td>
                    <td>{{ $a->maestro->nombre ?? '—' }}</td>
                    <td>{{ $a->tutor ?? '—' }}</td>
                    <td>{{ $a->celular ?? '—' }}</td>
                    <td>@if($a->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarAlumno({{ $a->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarAlumno({{ $a->id }}, '{{ $a->nombre }}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron alumnos con los filtros aplicados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $alumnos->links() }}
</div>

<div class="modal fade" id="modalAlumno" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                        <label class="form-label small fw-semibold">Edad</label>
                        <input type="text" class="form-control" id="alumno_edad" placeholder="Ej. 9 anos">
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
                        <label class="form-label small fw-semibold">Especialidad</label>
                        <select class="form-select" id="alumno_especialidad_id">
                            <option value="">-- Selecciona --</option>
                            @foreach($especialidades as $esp)
                                <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Maestro asignado</label>
                        <select class="form-select" id="alumno_maestro_id">
                            <option value="">-- Selecciona --</option>
                            @foreach($maestros as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                            @endforeach
                        </select>
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
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Direccion</label>
                    <input type="text" class="form-control" id="alumno_direccion">
                </div>
                <hr>
                <h6 class="fw-semibold small text-uppercase text-muted mb-2">Programar clases (opcional)</h6>
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <label class="form-label small fw-semibold">Periodo</label>
                        <select class="form-select" id="alumno_periodo_id">
                            <option value="">-- No programar clases ahora --</option>
                            @foreach($periodos as $p)
                                <option value="{{ $p->id }}"
                                    data-inicio="{{ $p->fecha_inicio->format('d/m/Y') }}"
                                    data-fin="{{ $p->fecha_fin->format('d/m/Y') }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="alumno_periodo_duracion"></small>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Salon</label>
                        <input type="text" class="form-control" id="alumno_horario_salon">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Dias y horario de clase</label>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr><th style="width:36px"></th><th>Dia</th><th>Hora inicio</th><th>Hora fin</th></tr>
                            </thead>
                            <tbody>
                                @foreach(['1'=>'Lunes','2'=>'Martes','3'=>'Miercoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sabado','7'=>'Domingo'] as $num => $label)
                                <tr>
                                    <td>
                                        <input class="form-check-input alumno-dia" type="checkbox" value="{{ $num }}"
                                               id="alumno_dia_{{ $num }}" onchange="toggleDiaHorario({{ $num }})">
                                    </td>
                                    <td><label for="alumno_dia_{{ $num }}" class="mb-0">{{ $label }}</label></td>
                                    <td><input type="time" class="form-control form-control-sm" id="alumno_dia_{{ $num }}_inicio" disabled></td>
                                    <td><input type="time" class="form-control form-control-sm" id="alumno_dia_{{ $num }}_fin" disabled></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">Cada dia puede tener una hora distinta (ej. Martes 4pm, Miercoles y Jueves 5pm). Al guardar, las clases se crean automaticamente en el calendario para todo el periodo.</small>
                </div>
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
@endsection

@push('scripts')
<script>
    const modalAlumno = new bootstrap.Modal('#modalAlumno');

    function nuevoAlumno() {
        document.getElementById('formAlumno').reset();
        document.getElementById('alumno_id').value = '';
        document.getElementById('alumno_periodo_duracion').innerText = '';
        for (let diaNum = 1; diaNum <= 7; diaNum++) {
            document.getElementById('alumno_dia_' + diaNum + '_inicio').disabled = true;
            document.getElementById('alumno_dia_' + diaNum + '_inicio').value = '';
            document.getElementById('alumno_dia_' + diaNum + '_fin').disabled = true;
            document.getElementById('alumno_dia_' + diaNum + '_fin').value = '';
        }
        document.getElementById('tituloModalAlumno').innerText = 'Nuevo Alumno';
    }

    function toggleDiaHorario(diaNum) {
        const checked = document.getElementById('alumno_dia_' + diaNum).checked;
        const inicio = document.getElementById('alumno_dia_' + diaNum + '_inicio');
        const fin = document.getElementById('alumno_dia_' + diaNum + '_fin');
        inicio.disabled = !checked;
        fin.disabled = !checked;
        if (!checked) {
            inicio.value = '';
            fin.value = '';
        }
    }

    document.getElementById('alumno_periodo_id').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const txt = document.getElementById('alumno_periodo_duracion');
        txt.innerText = this.value ? `Del ${opt.dataset.inicio} al ${opt.dataset.fin}` : '';
    });

    async function editarAlumno(id) {
        const res = await maFetch(`/alumnos/${id}/edit`);
        if (!res) return;
        const d = res.data;

        document.getElementById('alumno_id').value = d.id;
        document.getElementById('alumno_nombre').value = d.nombre;
        document.getElementById('alumno_edad').value = d.edad ?? '';
        document.getElementById('alumno_fecha_nacimiento').value = d.fecha_nacimiento ?? '';
        document.getElementById('alumno_dni').value = d.dni ?? '';
        document.getElementById('alumno_especialidad_id').value = d.especialidad_id ?? '';
        document.getElementById('alumno_maestro_id').value = d.maestro_id ?? '';
        document.getElementById('alumno_tutor').value = d.tutor ?? '';
        document.getElementById('alumno_celular').value = d.celular ?? '';
        document.getElementById('alumno_direccion').value = d.direccion ?? '';
        document.getElementById('alumno_fecha_ingreso').value = d.fecha_ingreso ?? '';
        document.getElementById('alumno_activo').checked = !!d.activo;
        document.getElementById('alumno_diagnostico').value = d.diagnostico ?? '';
        document.getElementById('alumno_observaciones').value = d.observaciones ?? '';

        // Precarga del horario/periodo: envuelto en try/catch para que, si falta
        // algun campo nuevo en el HTML, el modal se abra igual (no se rompe todo).
        try {
            const periodoSel = document.getElementById('alumno_periodo_id');
            const duracionTxt = document.getElementById('alumno_periodo_duracion');
            const salonInput = document.getElementById('alumno_horario_salon');

            for (let diaNum = 1; diaNum <= 7; diaNum++) {
                const cb = document.getElementById('alumno_dia_' + diaNum);
                const hi = document.getElementById('alumno_dia_' + diaNum + '_inicio');
                const hf = document.getElementById('alumno_dia_' + diaNum + '_fin');
                if (cb) cb.checked = false;
                if (hi) { hi.disabled = true; hi.value = ''; }
                if (hf) { hf.disabled = true; hf.value = ''; }
            }
            if (periodoSel) periodoSel.value = '';
            if (duracionTxt) duracionTxt.innerText = '';
            if (salonInput) salonInput.value = '';

            if (d.horarios && d.horarios.length) {
                d.horarios.forEach(h => {
                    const cb = document.getElementById('alumno_dia_' + h.dia_semana);
                    const hi = document.getElementById('alumno_dia_' + h.dia_semana + '_inicio');
                    const hf = document.getElementById('alumno_dia_' + h.dia_semana + '_fin');
                    if (cb) cb.checked = true;
                    if (hi) { hi.disabled = false; hi.value = (h.hora_inicio || '').substring(0, 5); }
                    if (hf) { hf.disabled = false; hf.value = (h.hora_fin || '').substring(0, 5); }
                });

                const h0 = d.horarios[0];
                if (salonInput) salonInput.value = h0.salon ?? '';
                if (h0.periodo_id && periodoSel) {
                    periodoSel.value = h0.periodo_id;
                    periodoSel.dispatchEvent(new Event('change'));
                }
            }
        } catch (err) {
            console.error('No se pudo precargar el horario del alumno:', err);
        }

        document.getElementById('tituloModalAlumno').innerText = 'Editar Alumno';
        modalAlumno.show();
    }

    document.getElementById('formAlumno').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('alumno_id').value;

        const periodoId = document.getElementById('alumno_periodo_id').value;
        const horariosDias = [];
        for (let dia = 1; dia <= 7; dia++) {
            const cb = document.getElementById('alumno_dia_' + dia);
            if (cb && cb.checked) {
                horariosDias.push({
                    dia_semana: dia,
                    hora_inicio: document.getElementById('alumno_dia_' + dia + '_inicio').value,
                    hora_fin: document.getElementById('alumno_dia_' + dia + '_fin').value,
                });
            }
        }

        if (horariosDias.length && !periodoId) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta el periodo',
                text: 'Marcaste dias de clase pero no seleccionaste un Periodo. Ve al menu "Periodos", crea uno (ej. Agosto 2026) y luego seleccionalo aqui para que las clases se generen en el calendario.',
            });
            return;
        }

        if (horariosDias.some(h => !h.hora_inicio || !h.hora_fin)) {
            Swal.fire({
                icon: 'warning',
                title: 'Horario incompleto',
                text: 'Completa la hora de inicio y de fin en cada dia que marcaste.',
            });
            return;
        }

        const payload = {
            nombre: document.getElementById('alumno_nombre').value,
            edad: document.getElementById('alumno_edad').value,
            fecha_nacimiento: document.getElementById('alumno_fecha_nacimiento').value || null,
            dni: document.getElementById('alumno_dni').value,
            especialidad_id: document.getElementById('alumno_especialidad_id').value || null,
            maestro_id: document.getElementById('alumno_maestro_id').value || null,
            tutor: document.getElementById('alumno_tutor').value,
            celular: document.getElementById('alumno_celular').value,
            direccion: document.getElementById('alumno_direccion').value,
            fecha_ingreso: document.getElementById('alumno_fecha_ingreso').value || null,
            activo: document.getElementById('alumno_activo').checked ? 1 : 0,
            diagnostico: document.getElementById('alumno_diagnostico').value,
            observaciones: document.getElementById('alumno_observaciones').value,
        };

        if (periodoId && horariosDias.length) {
            payload.periodo_id = periodoId;
            payload.horarios = horariosDias;
            payload.horario_salon = document.getElementById('alumno_horario_salon').value;
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
@endpush