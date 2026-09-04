@extends('layouts.app')
@section('titulo', 'Caja Chica')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Caja Chica</h5>
        <small class="text-muted">Movimientos menores de caja</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalCaja" onclick="nuevoMovimiento()"><i class="bi bi-plus-lg me-1"></i> Nuevo Movimiento</button>
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
            <thead><tr><th>Fecha</th><th>Proveedor</th><th>Descripcion</th><th>Monto</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($movimientos as $m)
                <tr>
                    <td>{{ $m->fecha->format('d/m/Y') }}</td>
                    <td>{{ $m->proveedor ?? '—' }}</td>
                    <td>{{ $m->descripcion }}</td>
                    <td class="fw-semibold">S/ {{ number_format($m->monto, 2) }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarMovimiento({{ $m->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarMovimiento({{ $m->id }}, '{{ $m->descripcion }}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No hay movimientos registrados para este periodo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $movimientos->links() }}
</div>

<div class="modal fade" id="modalCaja" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formCaja">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalCaja">Nuevo Movimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="caja_id">
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label small fw-semibold">Fecha</label><input type="date" class="form-control" id="caja_fecha" required></div>
                    <div class="col-6 mb-3"><label class="form-label small fw-semibold">Monto (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="caja_monto" required></div>
                </div>
                <div class="mb-3"><label class="form-label small fw-semibold">Proveedor</label><input type="text" class="form-control" id="caja_proveedor"></div>
                <div class="mb-1"><label class="form-label small fw-semibold">Descripcion</label><input type="text" class="form-control" id="caja_descripcion" required></div>
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
    const modalCaja = new bootstrap.Modal('#modalCaja');

    function nuevoMovimiento() {
        document.getElementById('formCaja').reset();
        document.getElementById('caja_id').value = '';
        document.getElementById('caja_fecha').value = new Date().toISOString().substring(0,10);
        document.getElementById('tituloModalCaja').innerText = 'Nuevo Movimiento';
    }

    async function editarMovimiento(id) {
        const res = await maFetch(`/caja-chica/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('caja_id').value = d.id;
        document.getElementById('caja_fecha').value = d.fecha.substring(0,10);
        document.getElementById('caja_monto').value = d.monto;
        document.getElementById('caja_proveedor').value = d.proveedor ?? '';
        document.getElementById('caja_descripcion').value = d.descripcion;
        document.getElementById('tituloModalCaja').innerText = 'Editar Movimiento';
        modalCaja.show();
    }

    document.getElementById('formCaja').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('caja_id').value;
        const payload = {
            fecha: document.getElementById('caja_fecha').value,
            monto: document.getElementById('caja_monto').value,
            proveedor: document.getElementById('caja_proveedor').value,
            descripcion: document.getElementById('caja_descripcion').value,
        };
        const url = id ? `/caja-chica/${id}` : '/caja-chica';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalCaja.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarMovimiento(id, descripcion) {
        if (!(await maConfirmarEliminar(descripcion))) return;
        const res = await maFetch(`/caja-chica/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
</script>
@endpush
