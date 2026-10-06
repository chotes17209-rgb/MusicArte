@extends('layouts.app')
@section('titulo', 'Calendario de Clases')

@push('estilos')
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.css" rel="stylesheet">
<style>
    #calendar { background: var(--superficie); border-radius: var(--radio); padding: 1rem; border: 1px solid var(--borde); }
    .fc { font-family: inherit; font-size: .8125rem; --fc-border-color: #efeeea; --fc-today-bg-color: #faf9ff; --fc-neutral-bg-color: var(--superficie-2); --fc-now-indicator-color: var(--rojo); }
    .fc-toolbar.fc-header-toolbar { margin-bottom: 1rem !important; flex-wrap: wrap; gap: .5rem; }
    .fc-toolbar-title { font-size: 1.05rem !important; font-weight: 600; color: var(--texto); }
    .fc-toolbar-title::first-letter { text-transform: uppercase; }
    .fc .fc-button { background: var(--superficie) !important; border: 1px solid var(--borde-fuerte) !important; color: var(--texto) !important; border-radius: var(--radio-sm) !important; font-size: .8125rem; font-weight: 500; box-shadow: none !important; padding: .35rem .7rem; text-transform: none; }
    .fc .fc-button:hover { background: var(--superficie-2) !important; }
    .fc .fc-button-active, .fc .fc-button:not(:disabled):active { background: var(--texto) !important; border-color: var(--texto) !important; color: #fff !important; }
    .fc .fc-button:disabled { opacity: .5; }
    .fc-col-header-cell { background: var(--superficie-2); font-weight: 500; padding: 6px 0; }
    .fc-col-header-cell-cushion { color: var(--texto-2); text-transform: capitalize; }
    .fc-daygrid-day-number { color: var(--texto-2); font-size: .78rem; }
    .fc-timegrid-slot { height: 2.6em; }
    .fc-timegrid-slot-label-cushion, .fc-timegrid-axis-cushion { font-size: .72rem; color: var(--texto-3); }
    .fc-scrollgrid { border-radius: var(--radio-sm); overflow: hidden; }

    /* Clase: fondo claro con el color de la especialidad y borde izquierdo */
    .fc .fc-event.clase { background: var(--c-fondo) !important; border: 0 !important; border-left: 3px solid var(--c) !important; border-radius: 4px !important; color: var(--texto) !important; box-shadow: none; cursor: pointer; }
    .fc .fc-event.clase:hover { filter: brightness(.97); }
    .fc .fc-event.clase .fc-event-main { color: var(--texto); }
    .clase-ev { padding: 2px 4px; line-height: 1.25; overflow: hidden; }
    .clase-ev .nombre { font-weight: 600; font-size: .75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .clase-ev .detalle { font-size: .7rem; color: var(--texto-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .clase-ev .hora { font-size: .68rem; color: var(--texto-3); }
    .clase.realizada .nombre::before { content: "✓ "; color: var(--verde); }
    .clase.cancelada { opacity: .55; }
    .clase.cancelada .nombre { text-decoration: line-through; }
    .fc-daygrid-event.clase { margin: 1px 2px !important; }
    .fc-list-event.clase td { background: transparent !important; }
    .fc-list-event-dot { border-color: var(--c) !important; }
    .fc .fc-list-day-cushion { background: var(--superficie-2) !important; }
    .fc-more-link { color: var(--acento); font-weight: 500; }

    .leyenda { display: flex; flex-wrap: wrap; gap: .4rem 1rem; align-items: center; font-size: .78rem; color: var(--texto-2); margin-bottom: .75rem; }
    .leyenda .punto { width: 10px; height: 10px; border-radius: 3px; display: inline-block; margin-right: .35rem; vertical-align: -1px; }
    .leyenda .sep { width: 1px; height: 14px; background: var(--borde); }
    .filtros-cal { display: grid; grid-template-columns: repeat(2, minmax(160px, 220px)) auto; gap: .5rem; }
    @media (max-width: 767px) {
        #calendar { padding: .5rem; }
        .filtros-cal { grid-template-columns: 1fr 1fr; width: 100%; }
        .filtros-cal .btn { grid-column: 1 / -1; }
        .fc-toolbar-title { font-size: .95rem !important; }
    }
</style>
@endpush

@section('contenido')
<x-page-head titulo="Calendario de clases" subtitulo="Toca una clase para ver o cambiar sus datos. En computadora también puedes arrastrarla a otro día u hora.">
    <div class="filtros-cal">
        <select class="form-select" id="filtroMaestro">
            <option value="">Todos los maestros</option>
            @foreach($maestros as $m)<option value="{{ $m->id }}">{{ $m->nombre }}</option>@endforeach
        </select>
        <select class="form-select" id="filtroAlumno">
            <option value="">Todos los alumnos</option>
            @foreach($alumnos as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
        </select>
        <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalClase" onclick="nuevaClase()"><i class="bi bi-plus-lg me-1"></i> Programar clase</button>
    </div>
</x-page-head>

<div class="leyenda">
    @foreach($especialidades as $e)
        <span><span class="punto" style="background: {{ $e->color }}"></span>{{ $e->nombre }}</span>
    @endforeach
    <span class="sep"></span>
    <span><span class="text-success fw-semibold">✓</span> Dictada</span>
    <span class="text-decoration-line-through text-muted">Cancelada</span>
    <span class="sep"></span>
    <span class="text-muted">Tip: elige un maestro para ver su semana completa.</span>
</div>

<div id="calendar"></div>

<!-- MODAL PROGRAMAR/EDITAR CLASE -->
<div class="modal fade" id="modalClase" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formClase">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalClase">Programar Clase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <label class="form-label small fw-semibold">Salón</label>
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
            // Seccion 5: la semana siempre inicia en Lunes, asi el Domingo
            // queda correctamente ubicado al lado derecho.
            firstDay: 1,
            // Con todos los maestros juntos, la semana tiene 15+ clases por hora: se
            // arranca en el dia. Al elegir un maestro o alumno se pasa a su semana.
            initialView: window.innerWidth < 768 ? 'listDay' : 'timeGridDay',
            headerToolbar: window.innerWidth < 768
                ? { left: 'prev,next', center: 'title', right: 'today' }
                : { left: 'prev,next today', center: 'title', right: 'timeGridDay,timeGridWeek,dayGridMonth,listWeek' },
            footerToolbar: window.innerWidth < 768 ? { center: 'listDay,listWeek,dayGridMonth' } : false,
            views: { listDay: { buttonText: 'Día' }, listWeek: { buttonText: 'Semana' } },
            buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
            slotEventOverlap: false,
            slotMinTime: '08:00:00',
            slotMaxTime: '21:00:00',
            slotLabelFormat: { hour: 'numeric', minute: '2-digit', hour12: true },
            allDaySlot: false,
            nowIndicator: true,
            dayMaxEvents: 4,
            moreLinkText: n => `+${n} más`,
            noEventsText: 'No hay clases en estas fechas',
            height: 'auto',
            editable: window.innerWidth >= 768,
            eventTimeFormat: { hour: 'numeric', minute: '2-digit', hour12: true },
            eventClassNames: info => ['clase', info.event.extendedProps.estado],
            eventDidMount: function (info) {
                const c = info.event.extendedProps.color || '#800080';
                info.el.style.setProperty('--c', c);
                info.el.style.setProperty('--c-fondo', c + '1f');
                const p = info.event.extendedProps;
                info.el.setAttribute('title', `${p.alumno}\n${p.especialidad} con ${p.maestro}${p.salon ? '\nSalón ' + p.salon : ''}`);
            },
            eventContent: function (arg) {
                const p = arg.event.extendedProps;
                if (arg.view.type.startsWith('list')) {
                    return { html: `<strong>${p.alumno}</strong> <span class="text-muted">· ${p.especialidad} con ${p.maestro}</span>${p.estado === 'realizada' ? ' <span class="badge bg-success ms-1">Dictada</span>' : ''}${p.estado === 'cancelada' ? ' <span class="badge bg-danger ms-1">Cancelada</span>' : ''}` };
                }
                return { html: `<div class="clase-ev"><div class="nombre">${p.alumno}</div><div class="detalle">${p.especialidad} · ${p.maestro}</div>${arg.view.type === 'dayGridMonth' ? `<div class="hora">${arg.timeText}</div>` : ''}</div>` };
            },
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
        });
        calendar.render();

        // Con un maestro o alumno elegido ya se puede leer la semana completa.
        function alCambiarFiltro() {
            const hayFiltro = document.getElementById('filtroMaestro').value || document.getElementById('filtroAlumno').value;
            const movil = window.innerWidth < 768;
            const vista = hayFiltro ? (movil ? 'listWeek' : 'timeGridWeek') : (movil ? 'listDay' : 'timeGridDay');
            if (calendar.view.type !== vista) calendar.changeView(vista); else calendar.refetchEvents();
        }
        document.getElementById('filtroMaestro').addEventListener('change', alCambiarFiltro);
        document.getElementById('filtroAlumno').addEventListener('change', alCambiarFiltro);
    });
</script>
@endpush
