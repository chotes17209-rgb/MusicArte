@extends('layouts.app')
@section('titulo', 'Egresos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Egresos</h5>
        <small class="text-muted">Gastos operativos del centro cultural</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalEgreso" onclick="nuevoEgreso()"><i class="bi bi-plus-lg me-1"></i> Nuevo Egreso</button>
</div>

<div class="card p-3 card-kpi mb-3" style="max-width:280px">
    <div class="text-muted small">Total del mes</div>
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

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Fecha</th><th>Detalle</th><th>Yape/BCP</th><th>Plin/IBK</th><th>Tarjeta</th><th>Efectivo</th><th>Total</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($egresos as $e)
                <tr>
                    <td>{{ $e->fecha->format('d/m/Y') }}</td>
                    <td>{{ $e->detalle }}</td>
                    <td>S/ {{ number_format($e->yape_bcp, 2) }}</td>
                    <td>S/ {{ number_format($e->plin_ibk, 2) }}</td>
                    <td>S/ {{ number_format($e->tarjeta, 2) }}</td>
                    <td>S/ {{ number_format($e->efectivo, 2) }}</td>
                    <td class="fw-semibold">S/ {{ number_format($e->total, 2) }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarEgreso({{ $e->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarEgreso({{ $e->id }}, '{{ $e->detalle }}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No hay egresos registrados para este periodo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $egresos->links() }}
</div>

<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formEgreso">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalEgreso">Nuevo Egreso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="egreso_id">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha</label>
                        <input type="date" class="form-control" id="egreso_fecha" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-semibold">Detalle</label>
                        <input type="text" class="form-control" id="egreso_detalle" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label small fw-semibold">Yape/BCP</label><input type="number" step="0.01" min="0" class="form-control" id="egreso_yape_bcp"></div>
                    <div class="col-6 mb-3"><label class="form-label small fw-semibold">Plin/IBK</label><input type="number" step="0.01" min="0" class="form-control" id="egreso_plin_ibk"></div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label small fw-semibold">Tarjeta</label><input type="number" step="0.01" min="0" class="form-control" id="egreso_tarjeta"></div>
                    <div class="col-6 mb-3"><label class="form-label small fw-semibold">Efectivo</label><input type="number" step="0.01" min="0" class="form-control" id="egreso_efectivo"></div>
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
    const modalEgreso = new bootstrap.Modal('#modalEgreso');

    function nuevoEgreso() {
        document.getElementById('formEgreso').reset();
        document.getElementById('egreso_id').value = '';
        document.getElementById('egreso_fecha').value = new Date().toISOString().substring(0,10);
        document.getElementById('tituloModalEgreso').innerText = 'Nuevo Egreso';
    }

    async function editarEgreso(id) {
        const res = await maFetch(`/egresos/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('egreso_id').value = d.id;
        document.getElementById('egreso_fecha').value = d.fecha.substring(0,10);
        document.getElementById('egreso_detalle').value = d.detalle;
        document.getElementById('egreso_yape_bcp').value = d.yape_bcp;
        document.getElementById('egreso_plin_ibk').value = d.plin_ibk;
        document.getElementById('egreso_tarjeta').value = d.tarjeta;
        document.getElementById('egreso_efectivo').value = d.efectivo;
        document.getElementById('tituloModalEgreso').innerText = 'Editar Egreso';
        modalEgreso.show();
    }

    document.getElementById('formEgreso').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('egreso_id').value;
        const payload = {
            fecha: document.getElementById('egreso_fecha').value,
            detalle: document.getElementById('egreso_detalle').value,
            yape_bcp: document.getElementById('egreso_yape_bcp').value || 0,
            plin_ibk: document.getElementById('egreso_plin_ibk').value || 0,
            tarjeta: document.getElementById('egreso_tarjeta').value || 0,
            efectivo: document.getElementById('egreso_efectivo').value || 0,
        };
        const url = id ? `/egresos/${id}` : '/egresos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalEgreso.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarEgreso(id, detalle) {
        if (!(await maConfirmarEliminar(detalle))) return;
        const res = await maFetch(`/egresos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
</script>
@endpush
