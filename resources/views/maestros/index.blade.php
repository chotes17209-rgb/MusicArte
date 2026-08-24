@extends('layouts.app')
@section('titulo', 'Maestros')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-semibold mb-0">Maestros</h5>
        <small class="text-muted">Plana docente del centro cultural. Un maestro puede ensenar varias especialidades.</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalMaestro" onclick="nuevoMaestro()">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Maestro
    </button>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nombre</th><th>Especialidades</th><th>Telefono</th><th>Email</th><th>Alumnos</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($maestros as $m)
                <tr>
                    <td class="fw-semibold">{{ $m->nombre }}</td>
                    <td>
                        @forelse($m->especialidades as $esp)
                            <span class="badge mb-1" style="background:{{ $esp->color }}" title="S/ {{ number_format($esp->pivot->tarifa_hora, 2) }} por hora">{{ $esp->nombre }} · S/{{ number_format($esp->pivot->tarifa_hora, 0) }}/h</span>
                        @empty
                            <span class="text-muted">—</span>
                        @endforelse
                    </td>
                    <td>{{ $m->telefono ?? '—' }}</td>
                    <td>{{ $m->email ?? '—' }}</td>
                    <td>{{ $m->alumnos_count }}</td>
                    <td>@if($m->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarMaestro({{ $m->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarMaestro({{ $m->id }}, '{{ $m->nombre }}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aun no hay maestros registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalMaestro" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formMaestro">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalMaestro">Nuevo Maestro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="maestro_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nombre completo</label>
                    <input type="text" class="form-control" id="maestro_nombre" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Especialidades que ensena y tarifa por hora (S/)</label>
                    <div class="border rounded p-2" style="max-height:220px;overflow-y:auto">
                        @foreach($especialidades as $esp)
                            <div class="row g-2 align-items-center mb-1">
                                <div class="col-7">
                                    <div class="form-check">
                                        <input class="form-check-input maestro-especialidad-check" type="checkbox" value="{{ $esp->id }}" id="esp_check_{{ $esp->id }}" onchange="toggleTarifaInput({{ $esp->id }})">
                                        <label class="form-check-label small" for="esp_check_{{ $esp->id }}">{{ $esp->nombre }}</label>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <input type="number" step="0.5" min="0" class="form-control form-control-sm maestro-tarifa-input" id="esp_tarifa_{{ $esp->id }}" placeholder="S//hora" disabled>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Marca las especialidades y define cuanto se le paga por hora en cada una (puede variar, ej. Piano S/10, Bateria S/15).</small>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Telefono</label>
                        <input type="text" class="form-control" id="maestro_telefono">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" class="form-control" id="maestro_email">
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="maestro_activo" checked>
                    <label class="form-check-label small">Maestro activo</label>
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
    const modalMaestro = new bootstrap.Modal('#modalMaestro');

    function limpiarChecksEspecialidad() {
        document.querySelectorAll('.maestro-especialidad-check').forEach(c => c.checked = false);
        document.querySelectorAll('.maestro-tarifa-input').forEach(i => { i.value = ''; i.disabled = true; });
    }

    function toggleTarifaInput(espId) {
        const chk = document.getElementById(`esp_check_${espId}`);
        const tarifa = document.getElementById(`esp_tarifa_${espId}`);
        tarifa.disabled = !chk.checked;
        if (!chk.checked) tarifa.value = '';
    }

    function nuevoMaestro() {
        document.getElementById('formMaestro').reset();
        document.getElementById('maestro_id').value = '';
        limpiarChecksEspecialidad();
        document.getElementById('tituloModalMaestro').innerText = 'Nuevo Maestro';
    }

    async function editarMaestro(id) {
        const res = await maFetch(`/maestros/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('maestro_id').value = d.id;
        document.getElementById('maestro_nombre').value = d.nombre;
        document.getElementById('maestro_telefono').value = d.telefono ?? '';
        document.getElementById('maestro_email').value = d.email ?? '';
        document.getElementById('maestro_activo').checked = !!d.activo;
        limpiarChecksEspecialidad();
        (d.especialidades || []).forEach(esp => {
            const chk = document.getElementById(`esp_check_${esp.id}`);
            const tarifa = document.getElementById(`esp_tarifa_${esp.id}`);
            if (chk) { chk.checked = true; }
            if (tarifa) { tarifa.disabled = false; tarifa.value = esp.pivot?.tarifa_hora ?? ''; }
        });
        document.getElementById('tituloModalMaestro').innerText = 'Editar Maestro';
        modalMaestro.show();
    }

    document.getElementById('formMaestro').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('maestro_id').value;
        const especialidades = Array.from(document.querySelectorAll('.maestro-especialidad-check:checked')).map(c => ({
            id: c.value,
            tarifa: document.getElementById(`esp_tarifa_${c.value}`).value || 0,
        }));
        const payload = {
            nombre: document.getElementById('maestro_nombre').value,
            especialidades: especialidades,
            telefono: document.getElementById('maestro_telefono').value,
            email: document.getElementById('maestro_email').value,
            activo: document.getElementById('maestro_activo').checked ? 1 : 0,
        };
        const url = id ? `/maestros/${id}` : '/maestros';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalMaestro.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarMaestro(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/maestros/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
</script>
@endpush
