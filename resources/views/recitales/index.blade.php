@extends('layouts.app')
@section('titulo', 'Recitales y Eventos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Recitales y Eventos</h5>
        <small class="text-muted">Presentaciones y eventos especiales del centro cultural</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalRecital" onclick="nuevoRecital()"><i class="bi bi-plus-lg me-1"></i> Nuevo Recital/Evento</button>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nombre</th><th>Fecha</th><th>Tema</th><th>Pago x alumno</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($recitales as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->nombre }}</td>
                    <td>{{ optional($r->fecha)->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $r->tema ?? '—' }}</td>
                    <td>{{ $r->pago_por_alumno ? 'S/ '.number_format($r->pago_por_alumno,2) : '—' }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarRecital({{ $r->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarRecital({{ $r->id }}, '{{ $r->nombre }}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Aun no hay recitales o eventos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalRecital" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formRecital">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalRecital">Nuevo Recital/Evento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="recital_id">
                <div class="row">
                    <div class="col-md-8 mb-3"><label class="form-label small fw-semibold">Nombre del evento</label><input type="text" class="form-control" id="recital_nombre" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label small fw-semibold">Fecha</label><input type="date" class="form-control" id="recital_fecha"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label small fw-semibold">Tema</label><input type="text" class="form-control" id="recital_tema"></div>
                    <div class="col-md-6 mb-3"><label class="form-label small fw-semibold">Pago por alumno (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="recital_pago"></div>
                </div>
                <div class="mb-3"><label class="form-label small fw-semibold">Descripcion</label><textarea class="form-control" id="recital_descripcion" rows="2"></textarea></div>
                <div class="mb-1"><label class="form-label small fw-semibold">Participantes</label><textarea class="form-control" id="recital_participantes" rows="2" placeholder="Nombres de los alumnos participantes"></textarea></div>
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
    const modalRecital = new bootstrap.Modal('#modalRecital');

    function nuevoRecital() {
        document.getElementById('formRecital').reset();
        document.getElementById('recital_id').value = '';
        document.getElementById('tituloModalRecital').innerText = 'Nuevo Recital/Evento';
    }

    async function editarRecital(id) {
        const res = await maFetch(`/recitales/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('recital_id').value = d.id;
        document.getElementById('recital_nombre').value = d.nombre;
        document.getElementById('recital_fecha').value = d.fecha ? d.fecha.substring(0,10) : '';
        document.getElementById('recital_tema').value = d.tema ?? '';
        document.getElementById('recital_pago').value = d.pago_por_alumno ?? '';
        document.getElementById('recital_descripcion').value = d.descripcion ?? '';
        document.getElementById('recital_participantes').value = d.participantes ?? '';
        document.getElementById('tituloModalRecital').innerText = 'Editar Recital/Evento';
        modalRecital.show();
    }

    document.getElementById('formRecital').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('recital_id').value;
        const payload = {
            nombre: document.getElementById('recital_nombre').value,
            fecha: document.getElementById('recital_fecha').value || null,
            tema: document.getElementById('recital_tema').value,
            pago_por_alumno: document.getElementById('recital_pago').value || null,
            descripcion: document.getElementById('recital_descripcion').value,
            participantes: document.getElementById('recital_participantes').value,
        };
        const url = id ? `/recitales/${id}` : '/recitales';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalRecital.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarRecital(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/recitales/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
</script>
@endpush
