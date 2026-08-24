@extends('layouts.app')
@section('titulo', 'Avisos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Avisos (ventanas flotantes)</h5>
        <small class="text-muted">Los avisos activos y vigentes aparecen como popup a todo el personal al ingresar</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalAviso" onclick="nuevoAviso()"><i class="bi bi-plus-lg me-1"></i> Nuevo Aviso</button>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Titulo</th><th>Tipo</th><th>Vigencia</th><th>Autor</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($avisos as $a)
                <tr>
                    <td class="fw-semibold">{{ $a->titulo }}</td>
                    <td>
                        @php $badge = ['info'=>'bg-primary','advertencia'=>'bg-warning text-dark','urgente'=>'bg-danger'][$a->tipo] @endphp
                        <span class="badge {{ $badge }}">{{ ucfirst($a->tipo) }}</span>
                    </td>
                    <td class="small">{{ optional($a->fecha_inicio)->format('d/m/Y') ?? 'Sin inicio' }} — {{ optional($a->fecha_fin)->format('d/m/Y') ?? 'Sin fin' }}</td>
                    <td>{{ $a->autor->name ?? '—' }}</td>
                    <td>@if($a->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarAviso({{ $a->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarAviso({{ $a->id }}, '{{ $a->titulo }}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aun no hay avisos publicados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $avisos->links() }}
</div>

<div class="modal fade" id="modalAviso" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formAviso">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalAviso">Nuevo Aviso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="aviso_id">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Titulo</label>
                    <input type="text" class="form-control" id="aviso_titulo" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Mensaje</label>
                    <textarea class="form-control" id="aviso_mensaje" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tipo</label>
                    <select class="form-select" id="aviso_tipo">
                        <option value="info">Informativo</option>
                        <option value="advertencia">Advertencia</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Vigente desde</label>
                        <input type="date" class="form-control" id="aviso_fecha_inicio">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Vigente hasta</label>
                        <input type="date" class="form-control" id="aviso_fecha_fin">
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="aviso_activo" checked>
                    <label class="form-check-label small">Aviso activo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Publicar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modalAviso = new bootstrap.Modal('#modalAviso');

    function nuevoAviso() {
        document.getElementById('formAviso').reset();
        document.getElementById('aviso_id').value = '';
        document.getElementById('tituloModalAviso').innerText = 'Nuevo Aviso';
    }

    async function editarAviso(id) {
        const res = await maFetch(`/avisos/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('aviso_id').value = d.id;
        document.getElementById('aviso_titulo').value = d.titulo;
        document.getElementById('aviso_mensaje').value = d.mensaje;
        document.getElementById('aviso_tipo').value = d.tipo;
        document.getElementById('aviso_fecha_inicio').value = d.fecha_inicio ? d.fecha_inicio.substring(0,10) : '';
        document.getElementById('aviso_fecha_fin').value = d.fecha_fin ? d.fecha_fin.substring(0,10) : '';
        document.getElementById('aviso_activo').checked = !!d.activo;
        document.getElementById('tituloModalAviso').innerText = 'Editar Aviso';
        modalAviso.show();
    }

    document.getElementById('formAviso').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('aviso_id').value;
        const payload = {
            titulo: document.getElementById('aviso_titulo').value,
            mensaje: document.getElementById('aviso_mensaje').value,
            tipo: document.getElementById('aviso_tipo').value,
            fecha_inicio: document.getElementById('aviso_fecha_inicio').value || null,
            fecha_fin: document.getElementById('aviso_fecha_fin').value || null,
            activo: document.getElementById('aviso_activo').checked ? 1 : 0,
        };
        const url = id ? `/avisos/${id}` : '/avisos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalAviso.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarAviso(id, titulo) {
        if (!(await maConfirmarEliminar(titulo))) return;
        const res = await maFetch(`/avisos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
</script>
@endpush
