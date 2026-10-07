@extends('layouts.app')
@section('titulo', 'Asistencia')

@push('estilos')
<style>
    /* Estado de asistencia bien visible de un vistazo. */
    .estado-grande .badge { font-size: .95rem; font-weight: 600; padding: .45rem .85rem; border-radius: 8px; }
    .estado-grande .badge::before { width: 8px; height: 8px; }
</style>
@endpush

@section('contenido')
@php $nombresEstado = ['asistio' => 'Asistió', 'falto' => 'Faltó', 'justificado' => 'Faltó con aviso', 'tardanza' => 'Tardanza']; @endphp
<x-page-head titulo="Asistencia" subtitulo="Marca con un toque si cada alumno asistió o faltó. Usa «Más» para faltas con aviso, tardanzas u observaciones.">
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
    <x-stat label="Asistieron" :valor="$clases->filter(fn ($c) => $c->asistencia && in_array($c->asistencia->estado, ['asistio', 'tardanza']))->count()" tono="verde" />
    <x-stat label="Faltaron" :valor="$clases->filter(fn ($c) => $c->asistencia && in_array($c->asistencia->estado, ['falto', 'justificado']))->count()" tono="rojo" />
</div>

<div class="card p-3 mb-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Hora</th><th>Alumno</th><th>Taller</th><th>Estado</th><th class="text-end">Marcar</th></tr></thead>
            <tbody>
            @forelse($clases as $c)
                <tr>
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($c->hora_inicio)->format('g:i a') }}</td>
                    <td class="fw-semibold">{{ $c->alumno->nombre }}</td>
                    <td>{{ $c->especialidad->nombre ?? '—' }} <span class="text-muted">· {{ $c->maestro->nombre ?? '—' }}</span></td>
                    <td class="estado-grande">
                        <span id="badge-asistencia-{{ $c->id }}"
                            class="badge {{ $c->asistencia ? ['asistio'=>'bg-success','falto'=>'bg-danger','justificado'=>'bg-warning','tardanza'=>'bg-info'][$c->asistencia->estado] : 'bg-secondary' }}">
                            {{ $c->asistencia ? ($nombresEstado[$c->asistencia->estado] ?? ucfirst($c->asistencia->estado)) : 'Sin marcar' }}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-light" onclick="marcarRapido({{ $c->id }}, 'asistio')"><i class="bi bi-check-lg text-success"></i> Asistió</button>
                            <button class="btn btn-light" onclick="marcarRapido({{ $c->id }}, 'falto')"><i class="bi bi-x-lg text-danger"></i> Faltó</button>
                            <button class="btn btn-light" onclick="marcarAsistencia({{ $c->id }}, @js($c->alumno->nombre), '{{ $c->asistencia->estado ?? '' }}')">Más</button>
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
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">Asistio</div><div class="fs-4 fw-bold text-success">{{ $resumenAlumno['asistio'] }}</div></div></div>
            <div class="col-md-4"><div class="card-kpi card p-3 text-center"><div class="text-muted small">% Asistencia</div><div class="fs-4 fw-bold">{{ $resumenAlumno['porcentaje'] }}%</div></div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Fecha</th><th>Estado</th><th>Observación</th></tr></thead>
                <tbody>
                @foreach($resumenAlumno['detalle'] as $d)
                    <tr>
                        <td>{{ optional($d->clase)->fecha?->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($d->estado) }}</td>
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
                        <option value="asistio">Asistió</option>
                        <option value="falto">Faltó (sin aviso)</option>
                        <option value="justificado">Faltó con aviso</option>
                        <option value="tardanza">Tardanza</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Observación (opcional)</label>
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
@endsection

@push('scripts')
<script>
    const modalAsistencia = new bootstrap.Modal('#modalAsistencia');

    const BADGE_CLASES = {
        asistio: 'bg-success',
        falto: 'bg-danger',
        justificado: 'bg-warning',
        tardanza: 'bg-info text-dark',
    };

    function pintarBadge(claseId, estado) {
        const badge = document.getElementById(`badge-asistencia-${claseId}`);
        if (!badge) return;
        badge.className = 'badge ' + (BADGE_CLASES[estado] || 'bg-secondary');
        const NOMBRES = { asistio: 'Asistió', falto: 'Faltó', justificado: 'Faltó con aviso', tardanza: 'Tardanza' };
        badge.textContent = estado ? (NOMBRES[estado] || estado) : 'Sin marcar';
    }

    // Botones rapidos de la fila: marcan al instante, sin abrir la ventana.
    async function marcarRapido(claseId, estado = 'asistio') {
        const res = await maFetch(`/asistencia/${claseId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado }),
        });
        if (res && res.ok) {
            pintarBadge(claseId, estado);
            maToast('success', estado === 'asistio' ? 'Marcado: asistió' : 'Marcado: faltó');
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
@endpush