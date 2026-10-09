@extends('layouts.app')
@section('titulo', 'Asistencia')

@push('estilos')
<style>
    /* Estado de asistencia bien visible de un vistazo. */
    .estado-grande .badge { font-size: .95rem; font-weight: 600; padding: .45rem .85rem; border-radius: 8px; }
    .estado-grande .badge::before { width: 8px; height: 8px; }

    /* Botones A / F / R / S: el marcado queda pintado. */
    .marcador { display: inline-flex; gap: .3rem; align-items: center; }
    .marcador .btn-letra { width: 2.4rem; height: 2.4rem; padding: 0; font-weight: 700; font-size: 1rem; border-radius: 8px;
        border: 2px solid var(--borde, #dee2e6); background: #fff; color: #555; }
    .marcador .btn-letra:hover { border-color: var(--acento); color: var(--acento); }
    .marcador .btn-letra.activo[data-estado="asistio"] { background: #198754; border-color: #198754; color: #fff; }
    .marcador .btn-letra.activo[data-estado="falto"] { background: #dc3545; border-color: #dc3545; color: #fff; }
    .marcador .btn-letra.activo[data-estado="recupero"] { background: var(--acento); border-color: var(--acento); color: #fff; }
    .marcador .btn-letra.activo[data-estado="sin_marcar"] { background: #6c757d; border-color: #6c757d; color: #fff; }
    .marcador .btn-mas { height: 2.4rem; border-radius: 8px; border: 2px solid var(--borde, #dee2e6); background: #fff; }
    .marcador .btn-mas.con-nota { border-color: var(--acento); color: var(--acento); }
    .nota-asistencia { font-size: .85rem; color: #6c757d; font-style: italic; }
    .leyenda-letras span { margin-right: .9rem; white-space: nowrap; }
</style>
@endpush

@section('contenido')
@php
    $nombresEstado = \App\Models\Asistencia::NOMBRES;
    $colorEstado = ['asistio' => 'bg-success', 'falto' => 'bg-danger', 'justificado' => 'bg-warning', 'tardanza' => 'bg-info', 'recupero' => 'bg-primary'];
    $letras = ['asistio' => ['A', 'Asistió'], 'falto' => ['F', 'Faltó'], 'recupero' => ['R', 'Recuperó'], 'sin_marcar' => ['S', 'Sin marcar']];
@endphp
<x-page-head titulo="Asistencia" subtitulo="Marca con un toque: A asistió, F faltó, R recuperó, S sin marcar. Con «Nota» agregas una observación.">
    <form method="GET" data-autofiltro>
        <select name="maestro_id" class="form-select" title="Filtrar por maestro">
            <option value="">Todos los maestros</option>
            @foreach($maestros as $m)
                <option value="{{ $m->id }}" @selected(request('maestro_id')==$m->id)>{{ $m->nombre }}</option>
            @endforeach
        </select>
        <input type="date" name="fecha" value="{{ $fecha }}" class="form-control">
    </form>
</x-page-head>

<div class="stats">
    <x-stat label="Clases del día" :valor="$clases->count()" :detalle="\Carbon\Carbon::parse($fecha)->translatedFormat('l d/m')" />
    <x-stat label="Marcadas" :valor="$clases->filter(fn ($c) => $c->asistencia)->count()" />
    <x-stat label="Asistieron" :valor="$clases->filter(fn ($c) => $c->asistencia && in_array($c->asistencia->estado, \App\Models\Asistencia::PRESENTES))->count()" tono="verde" detalle="incluye recuperadas" />
    <x-stat label="Faltaron" :valor="$clases->filter(fn ($c) => $c->asistencia && in_array($c->asistencia->estado, ['falto', 'justificado']))->count()" tono="rojo" />
</div>

<div class="card p-3 mb-3">
    <div class="leyenda-letras small text-muted mb-2">
        <span><b class="text-success">A</b> Asistió</span><span><b class="text-danger">F</b> Faltó</span><span><b class="text-primary">R</b> Recuperó</span><span><b>S</b> Sin marcar</span><span><i class="bi bi-chat-left-text"></i> Nota: observación, falta con aviso o tardanza</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Hora</th><th>Alumno</th><th>Taller</th><th>Estado</th><th class="text-end">Marcar</th></tr></thead>
            <tbody>
            @forelse($clases as $c)
                @php $estadoActual = $c->asistencia->estado ?? 'sin_marcar'; @endphp
                <tr id="fila-asistencia-{{ $c->id }}">
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($c->hora_inicio)->format('g:i a') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $c->alumno->nombre }}</div>
                        <div class="nota-asistencia" id="nota-asistencia-{{ $c->id }}" @if(! $c->asistencia?->observacion) hidden @endif>
                            <i class="bi bi-chat-left-text"></i> <span>{{ $c->asistencia?->observacion }}</span>
                        </div>
                    </td>
                    <td>{{ $c->especialidad->nombre ?? '—' }} <span class="text-muted">· {{ $c->maestro->nombre ?? '—' }}</span></td>
                    <td class="estado-grande">
                        <span id="badge-asistencia-{{ $c->id }}"
                            class="badge {{ $c->asistencia ? ($colorEstado[$c->asistencia->estado] ?? 'bg-secondary') : 'bg-secondary' }}">
                            {{ $c->asistencia ? $c->asistencia->estadoLabel() : 'Sin marcar' }}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="marcador" id="marcador-{{ $c->id }}">
                            @foreach($letras as $estado => [$letra, $titulo])
                                <button type="button" class="btn btn-letra {{ $estadoActual === $estado ? 'activo' : '' }}" data-estado="{{ $estado }}"
                                        title="{{ $titulo }}" aria-label="{{ $titulo }}" onclick="marcarRapido({{ $c->id }}, '{{ $estado }}')">{{ $letra }}</button>
                            @endforeach
                            <button type="button" class="btn btn-mas {{ $c->asistencia?->observacion ? 'con-nota' : '' }}" id="btn-nota-{{ $c->id }}" title="Agregar nota u otra opción"
                                    onclick="marcarAsistencia({{ $c->id }}, @js($c->alumno->nombre))"
                                    data-estado="{{ $c->asistencia->estado ?? '' }}" data-observacion="{{ $c->asistencia?->observacion }}">
                                <i class="bi bi-chat-left-text"></i> Nota
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="vacio"><i class="bi bi-calendar-x"></i>No hay clases para esta fecha.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card p-3">
    <h6 class="fw-semibold mb-3">Resumen de asistencia por alumno</h6>
    <form method="GET" class="row g-2 mb-3" data-autofiltro>
        <input type="hidden" name="fecha" value="{{ $fecha }}">
        <input type="hidden" name="maestro_id" value="{{ request('maestro_id') }}">
        <div class="col-md-5">
            <select name="alumno_id" class="form-select">
                <option value="">-- Selecciona un alumno --</option>
                @foreach($alumnos as $a)
                    <option value="{{ $a->id }}" @selected(request('alumno_id')==$a->id)>{{ $a->nombre }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($resumenAlumno)
        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Total clases</div><div class="fs-4 fw-bold">{{ $resumenAlumno['total'] }}</div></div></div>
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Asistió</div><div class="fs-4 fw-bold text-success">{{ $resumenAlumno['asistio'] }}</div><div class="small text-muted">incluye recuperadas</div></div></div>
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">% Asistencia</div><div class="fs-4 fw-bold">{{ $resumenAlumno['porcentaje'] }}%</div></div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Fecha</th><th>Estado</th><th>Observación</th></tr></thead>
                <tbody>
                @foreach($resumenAlumno['detalle'] as $d)
                    <tr>
                        <td>{{ optional($d->clase)->fecha?->format('d/m/Y') }}</td>
                        <td>{{ $d->estadoLabel() }}</td>
                        <td>{{ $d->observacion ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- MODAL MARCAR ASISTENCIA -->
<div class="modal fade" id="modalAsistencia" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formAsistencia">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalAsistencia">Marcar Asistencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="asistencia_clase_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Estado</label>
                    <select class="form-select" id="asistencia_estado" required>
                        <option value="asistio">A · Asistió</option>
                        <option value="falto">F · Faltó (sin aviso)</option>
                        <option value="recupero">R · Recuperó (faltó y luego recuperó la clase)</option>
                        <option value="justificado">Faltó con aviso</option>
                        <option value="tardanza">Tardanza</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Observación (opcional)</label>
                    <textarea class="form-control" id="asistencia_observacion" rows="3" maxlength="1000" placeholder="Ej.: recuperó el sábado 4/10, avisó que estaba enfermo..."></textarea>
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
    const modalAsistencia = new bootstrap.Modal('#modalAsistencia');

    const BADGE_CLASES = {
        asistio: 'bg-success',
        falto: 'bg-danger',
        justificado: 'bg-warning',
        tardanza: 'bg-info text-dark',
        recupero: 'bg-primary',
    };
    const NOMBRES = @json(\App\Models\Asistencia::NOMBRES);
    const TOAST = { asistio: 'Marcado: asistió', falto: 'Marcado: faltó', recupero: 'Marcado: recuperó', sin_marcar: 'Quedó sin marcar' };

    // Pinta la fila: etiqueta de estado, boton A/F/R/S activo y la nota.
    function pintarFila(claseId, estado, observacion) {
        const badge = document.getElementById(`badge-asistencia-${claseId}`);
        if (badge) {
            badge.className = 'badge ' + (BADGE_CLASES[estado] || 'bg-secondary');
            badge.textContent = estado && estado !== 'sin_marcar' ? (NOMBRES[estado] || estado) : 'Sin marcar';
        }
        document.querySelectorAll(`#marcador-${claseId} .btn-letra`).forEach(b => {
            b.classList.toggle('activo', b.dataset.estado === (estado || 'sin_marcar'));
        });

        const btnNota = document.getElementById(`btn-nota-${claseId}`);
        if (estado === 'sin_marcar') observacion = '';
        if (btnNota) {
            btnNota.dataset.estado = estado === 'sin_marcar' ? '' : estado;
            if (observacion !== undefined) btnNota.dataset.observacion = observacion || '';
            btnNota.classList.toggle('con-nota', !!btnNota.dataset.observacion);
        }
        const nota = document.getElementById(`nota-asistencia-${claseId}`);
        if (nota && observacion !== undefined) {
            nota.querySelector('span').textContent = observacion || '';
            nota.hidden = !observacion;
        }
    }

    // Botones A / F / R / S: marcan al instante y conservan la nota.
    async function marcarRapido(claseId, estado = 'asistio') {
        const res = await maFetch(`/asistencia/${claseId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado }),
        });
        if (res && res.ok) {
            pintarFila(claseId, estado, estado === 'sin_marcar' ? '' : undefined);
            maToast('success', TOAST[estado] || 'Asistencia registrada');
        }
    }

    // Boton "Nota": abre la ventana con el estado y la observacion guardados.
    function marcarAsistencia(claseId, alumnoNombre) {
        const btn = document.getElementById(`btn-nota-${claseId}`);
        document.getElementById('asistencia_clase_id').value = claseId;
        document.getElementById('asistencia_estado').value = btn.dataset.estado || 'asistio';
        document.getElementById('asistencia_observacion').value = btn.dataset.observacion || '';
        document.getElementById('tituloModalAsistencia').innerText = `Asistencia — ${alumnoNombre}`;
        modalAsistencia.show();
    }

    document.getElementById('formAsistencia').addEventListener('submit', async (e) => {
        e.preventDefault();
        const claseId = document.getElementById('asistencia_clase_id').value;
        const payload = {
            estado: document.getElementById('asistencia_estado').value,
            observacion: document.getElementById('asistencia_observacion').value.trim(),
        };
        const res = await maFetch(`/asistencia/${claseId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            pintarFila(claseId, payload.estado, payload.observacion);
            maToast('success', res.message);
            modalAsistencia.hide();
        }
    });
</script>
@endpush