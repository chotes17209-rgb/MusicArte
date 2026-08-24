@extends('layouts.app')
@section('titulo', 'Calendario de Clases')

@push('estilos')
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.css" rel="stylesheet">
<style>
    #calendar { background: #fff; border-radius: 14px; padding: 1rem; border: 1px solid #edeef5; }
    .fc-event { cursor: pointer; border: none !important; padding: 2px 4px; }
    .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 600; color: #3d2c8d; }
    .fc-button-primary { background: #3d2c8d !important; border-color: #3d2c8d !important; }
    .fc-button-primary:hover { background: #2a1e63 !important; }
</style>
@endpush

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Calendario de Clases</h5>
        <small class="text-muted">Arrastra una clase para reprogramarla. Haz clic para ver/editar detalles.</small>
    </div>
    <div class="d-flex gap-2">
        <select class="form-select form-select-sm" id="filtroMaestro" style="width:180px">
            <option value="">Todos los maestros</option>
            @foreach($maestros as $m)<option value="{{ $m->id }}">{{ $m->nombre }}</option>@endforeach
        </select>
        <select class="form-select form-select-sm" id="filtroAlumno" style="width:180px">
            <option value="">Todos los alumnos</option>
            @foreach($alumnos as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
        </select>
        <button class="btn btn-morado btn-sm" data-bs-toggle="modal" data-bs-target="#modalClase" onclick="nuevaClase()"><i class="bi bi-plus-lg me-1"></i> Programar Clase</button>
    </div>
</div>

<div id="calendar"></div>

<!-- MODAL PROGRAMAR/EDITAR CLASE -->
<div class="modal fade" id="modalClase" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formClase">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalClase">Programar Clase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="clase_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alumno</label>
                    <select class="form-select" id="clase_alumno_id" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($alumnos as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Maestro</label>
                    <select class="form-select" id="clase_maestro_id">
                        <option value="">-- Selecciona --</option>
                        @foreach($maestros as $m)<option value="{{ $m->id }}">{{ $m->nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha</label>
                        <input type="date" class="form-control" id="clase_fecha" required>
                    </div>
                    <div class="col-3 mb-3">
                        <label class="form-label small fw-semibold">Inicio</label>
                        <input type="time" class="form-control" id="clase_hora_inicio" required>
                    </div>
                    <div class="col-3 mb-3">
                        <label class="form-label small fw-semibold">Fin</label>
                        <input type="time" class="form-control" id="clase_hora_fin" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Salon</label>
                        <input type="text" class="form-control" id="clase_salon">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select class="form-select" id="clase_estado">
                            <option value="programada">Programada</option>
                            <option value="realizada">Realizada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Notas</label>
                    <textarea class="form-control" id="clase_notas" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger" id="btnEliminarClase" onclick="eliminarClaseActual()" style="display:none"><i class="bi bi-trash"></i> Eliminar</button>
                <div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-morado">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/locales/es.global.min.js"></script>
<script>
    const modalClase = new bootstrap.Modal('#modalClase');
    let calendar;

    function nuevaClase() {
        document.getElementById('formClase').reset();
        document.getElementById('clase_id').value = '';
        document.getElementById('btnEliminarClase').style.display = 'none';
        document.getElementById('tituloModalClase').innerText = 'Programar Clase';
    }

    async function abrirClase(id) {
        const res = await maFetch(`/clases/${id}`);
        if (!res) return;
        const d = res.data;
        document.getElementById('clase_id').value = d.id;
        document.getElementById('clase_alumno_id').value = d.alumno_id;
        document.getElementById('clase_maestro_id').value = d.maestro_id ?? '';
        document.getElementById('clase_fecha').value = d.fecha.substring(0,10);
        document.getElementById('clase_hora_inicio').value = d.hora_inicio.substring(0,5);
        document.getElementById('clase_hora_fin').value = d.hora_fin.substring(0,5);
        document.getElementById('clase_salon').value = d.salon ?? '';
        document.getElementById('clase_estado').value = d.estado;
        document.getElementById('clase_notas').value = d.notas ?? '';
        document.getElementById('btnEliminarClase').style.display = 'inline-block';
        document.getElementById('tituloModalClase').innerText = 'Editar Clase';
        modalClase.show();
    }

    document.getElementById('formClase').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('clase_id').value;
        const payload = {
            alumno_id: document.getElementById('clase_alumno_id').value,
            maestro_id: document.getElementById('clase_maestro_id').value || null,
            fecha: document.getElementById('clase_fecha').value,
            hora_inicio: document.getElementById('clase_hora_inicio').value,
            hora_fin: document.getElementById('clase_hora_fin').value,
            salon: document.getElementById('clase_salon').value,
            estado: document.getElementById('clase_estado').value,
            notas: document.getElementById('clase_notas').value,
        };
        const url = id ? `/clases/${id}` : '/clases';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalClase.hide();
            calendar.refetchEvents();
        }
    });

    async function eliminarClaseActual() {
        const id = document.getElementById('clase_id').value;
        if (!id) return;
        if (!(await maConfirmarEliminar('esta clase'))) return;
        const res = await maFetch(`/clases/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            modalClase.hide();
            calendar.refetchEvents();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'timeGridWeek',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
            slotMinTime: '08:00:00',
            slotMaxTime: '21:00:00',
            allDaySlot: false,
            height: 'auto',
            editable: true,
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            events: function (info, success, failure) {
                const params = new URLSearchParams({
                    start: info.startStr, end: info.endStr,
                    maestro_id: document.getElementById('filtroMaestro').value,
                    alumno_id: document.getElementById('filtroAlumno').value,
                });
                fetch(`/calendario/eventos?${params}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(success).catch(failure);
            },
            eventClick: function (info) { abrirClase(info.event.id); },
            eventDrop: async function (info) {
                const ev = info.event;
                const res = await maFetch(`/clases/${ev.id}/mover`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fecha: ev.startStr.substring(0,10),
                        hora_inicio: ev.startStr.substring(11,16),
                        hora_fin: ev.endStr.substring(11,16),
                    }),
                });
                if (res && res.ok) { maToast('success', res.message); } else { info.revert(); }
            },
            eventDidMount: function (info) {
                const tip = `${info.event.extendedProps.alumno} — ${info.event.extendedProps.especialidad}\nMaestro: ${info.event.extendedProps.maestro}\nSalon: ${info.event.extendedProps.salon ?? '—'}`;
                info.el.setAttribute('title', tip);
            },
        });
        calendar.render();

        document.getElementById('filtroMaestro').addEventListener('change', () => calendar.refetchEvents());
        document.getElementById('filtroAlumno').addEventListener('change', () => calendar.refetchEvents());
    });
</script>
@endpush
