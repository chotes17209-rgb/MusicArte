@extends('layouts.app')
@section('titulo', 'Planilla Maestros')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Planilla de pago a Maestros</h5>
        <small class="text-muted">{{ \App\Models\Pago::MESES[$mes] }} {{ $anio }} — calculada desde la asistencia de los alumnos</small>
    </div>
    @auth @if(auth()->user()->esAdmin())
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalGenerar"><i class="bi bi-calculator me-1"></i> Generar desde Asistencia</button>
        <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalPlanilla" onclick="nuevaPlanilla()"><i class="bi bi-plus-lg me-1"></i> Agregar manual</button>
    </div>
    @else
    <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Modo solo lectura</span>
    @endif @endauth
</div>

<div class="card p-3 card-kpi mb-3" style="max-width:280px">
    <div class="text-muted small">Total planilla del mes</div>
    <div class="fs-4 fw-bold text-danger">S/ {{ number_format($totalMes, 2) }}</div>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-4">
            <select name="mes" class="form-select">
                @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><input type="number" name="anio" value="{{ $anio }}" class="form-control"></div>
        <div class="col-md-2"><button class="btn btn-light w-100">Filtrar</button></div>
    </form>
</div>

@if($planillas->isEmpty())
    <div class="alert alert-secondary">
        No hay planilla generada para este periodo todavia.
        @auth @if(auth()->user()->esAdmin())
        Marca primero la asistencia de las clases del mes y luego usa <strong>"Generar desde Asistencia"</strong>.
        @endif @endauth
    </div>
@endif

@foreach($planillas as $grupo)
<div class="card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-semibold mb-0"><i class="bi bi-person-badge me-1"></i> MAESTRO: {{ strtoupper($grupo['maestro']->nombre ?? 'Sin asignar') }}</h6>
        <span class="fw-bold" style="color:#3d2c8d">S/ {{ number_format($grupo['total_monto'], 2) }} &middot; {{ $grupo['total_horas'] }} horas</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Estudiante</th><th>Taller</th><th>Horas</th><th>Monto</th><th>Observ.</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @foreach($grupo['items'] as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                    <td>{{ $p->especialidad->nombre ?? '—' }}</td>
                    <td>{{ $p->horas }}</td>
                    <td>S/ {{ number_format($p->monto, 2) }}</td>
                    <td class="small text-muted">{{ $p->observacion ?? '' }}</td>
                    <td class="text-end">
                        @auth @if(auth()->user()->esAdmin())
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarPlanilla({{ $p->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarPlanilla({{ $p->id }}, '{{ $p->alumno->nombre ?? '' }}')"><i class="bi bi-trash"></i></button>
                        @endif @endauth
                    </td>
                </tr>
            @endforeach
            <tr class="fw-bold"><td colspan="3">TOTAL {{ strtoupper($grupo['maestro']->nombre ?? '') }}</td><td>S/ {{ number_format($grupo['total_monto'], 2) }}</td><td colspan="2"></td></tr>
            </tbody>
        </table>
    </div>
</div>
@endforeach

@auth @if(auth()->user()->esAdmin())
<!-- MODAL GENERAR DESDE ASISTENCIA -->
<div class="modal fade" id="modalGenerar" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formGenerar">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title">Generar planilla desde asistencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">
                    Se calculan las horas dictadas por cada maestro segun la asistencia marcada
                    (estado "Asistio" o "Tardanza") y se multiplican por la tarifa/hora configurada
                    en la ficha de cada maestro. Puedes editar cualquier monto despues.
                </p>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Mes</label>
                        <select class="form-select" id="generar_mes">
                            @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Ano</label>
                        <input type="number" class="form-control" id="generar_anio" value="{{ $anio }}">
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

<!-- MODAL AGREGAR/EDITAR MANUAL -->
<div class="modal fade" id="modalPlanilla" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formPlanilla">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalPlanilla">Agregar registro manual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="planilla_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Maestro</label>
                    <select class="form-select" id="planilla_maestro_id" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($maestros as $m)<option value="{{ $m->id }}">{{ $m->nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alumno</label>
                    <select class="form-select" id="planilla_alumno_id">
                        <option value="">-- Selecciona --</option>
                        @foreach($alumnos as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Especialidad (Taller)</label>
                    <select class="form-select" id="planilla_especialidad_id">
                        <option value="">-- Selecciona --</option>
                        @foreach($especialidades as $esp)<option value="{{ $esp->id }}">{{ $esp->nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-4 mb-3"><label class="form-label small fw-semibold">Mes</label>
                        <select class="form-select" id="planilla_mes">
                            @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-4 mb-3"><label class="form-label small fw-semibold">Ano</label><input type="number" class="form-control" id="planilla_anio" value="{{ $anio }}"></div>
                    <div class="col-4 mb-3"><label class="form-label small fw-semibold">Horas</label><input type="number" step="0.5" min="0" class="form-control" id="planilla_horas"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Monto a pagar (S/)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="planilla_monto" required>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Observacion</label>
                    <input type="text" class="form-control" id="planilla_observacion" placeholder="Ej. OJO, 1R (recuperacion), etc.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endif @endauth
@endsection

@push('scripts')
<script>
@auth @if(auth()->user()->esAdmin())
    const modalPlanilla = new bootstrap.Modal('#modalPlanilla');
    const modalGenerar = new bootstrap.Modal('#modalGenerar');

    function nuevaPlanilla() {
        document.getElementById('formPlanilla').reset();
        document.getElementById('planilla_id').value = '';
        document.getElementById('tituloModalPlanilla').innerText = 'Agregar registro manual';
    }

    async function editarPlanilla(id) {
        const res = await maFetch(`/planilla/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('planilla_id').value = d.id;
        document.getElementById('planilla_maestro_id').value = d.maestro_id;
        document.getElementById('planilla_alumno_id').value = d.alumno_id ?? '';
        document.getElementById('planilla_especialidad_id').value = d.especialidad_id ?? '';
        document.getElementById('planilla_mes').value = d.mes;
        document.getElementById('planilla_anio').value = d.anio;
        document.getElementById('planilla_horas').value = d.horas;
        document.getElementById('planilla_monto').value = d.monto;
        document.getElementById('planilla_observacion').value = d.observacion ?? '';
        document.getElementById('tituloModalPlanilla').innerText = 'Editar registro';
        modalPlanilla.show();
    }

    document.getElementById('formPlanilla').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('planilla_id').value;
        const payload = {
            maestro_id: document.getElementById('planilla_maestro_id').value,
            alumno_id: document.getElementById('planilla_alumno_id').value || null,
            especialidad_id: document.getElementById('planilla_especialidad_id').value || null,
            mes: document.getElementById('planilla_mes').value,
            anio: document.getElementById('planilla_anio').value,
            horas: document.getElementById('planilla_horas').value || 0,
            monto: document.getElementById('planilla_monto').value,
            observacion: document.getElementById('planilla_observacion').value,
        };
        const url = id ? `/planilla/${id}` : '/planilla';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalPlanilla.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarPlanilla(id, nombre) {
        if (!(await maConfirmarEliminar(`el pago de ${nombre}`))) return;
        const res = await maFetch(`/planilla/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }

    document.getElementById('formGenerar').addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            mes: document.getElementById('generar_mes').value,
            anio: document.getElementById('generar_anio').value,
        };
        const res = await maFetch('/planilla/generar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalGenerar.hide();
            setTimeout(() => location.reload(), 900);
        }
    });
@endif @endauth
</script>
@endpush
