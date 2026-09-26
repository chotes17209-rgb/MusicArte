@extends('layouts.app')
@section('titulo', 'Pagos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Pagos / Mensualidades</h5>
        <small class="text-muted">{{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}</small>
    </div>
    @auth @if(auth()->user()->esAdmin())
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalPago" onclick="nuevoPago()"><i class="bi bi-plus-lg me-1"></i> Registrar Pago</button>
    @else
    <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Modo solo lectura (precios reservados al administrador)</span>
    @endif @endauth
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Recaudado (filtro actual)</div><div class="fs-4 fw-bold text-success">S/ {{ number_format($totales['recaudado'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Pendiente de cobro</div><div class="fs-4 fw-bold text-danger">S/ {{ number_format($totales['pendiente'], 2) }}</div></div></div>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-2">
            <select name="mes" class="form-select">
                @foreach(\App\Models\Pago::MESES as $num => $nombre)
                    <option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <input type="number" name="anio" value="{{ $anio }}" class="form-control">
        </div>
        <div class="col-md-2">
            <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar alumno...">
        </div>
        <div class="col-md-2">
            <select name="especialidad_id" class="form-select">
                <option value="">Todos los talleres</option>
                @foreach($especialidades as $e)
                    <option value="{{ $e->id }}" @selected(request('especialidad_id')==$e->id)>{{ $e->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="maestro_id" class="form-select">
                <option value="">Todos los maestros</option>
                @foreach($maestros as $m)
                    <option value="{{ $m->id }}" @selected(request('maestro_id')==$m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="pendiente" @selected(request('estado')=='pendiente')>Pendiente</option>
                <option value="a_cuenta" @selected(request('estado')=='a_cuenta')>A cuenta</option>
                <option value="pagado" @selected(request('estado')=='pagado')>Pagado</option>
            </select>
        </div>
        <div class="col-md-1 d-grid"><button class="btn btn-light w-100">Filtrar</button></div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Taller</th><th>Concepto</th><th>Total</th><th>Abonado</th><th>Saldo</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($pagos as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                    <td>{{ $p->alumnoTaller->especialidad->nombre ?? '—' }}</td>
                    <td>{{ $p->concepto ?? '—' }}</td>
                    <td>S/ {{ number_format($p->monto_total, 2) }}</td>
                    <td>S/ {{ number_format($p->monto_total - $p->saldo, 2) }}</td>
                    <td class="{{ $p->saldo > 0 ? 'text-danger fw-semibold' : 'text-success' }}">S/ {{ number_format($p->saldo, 2) }}</td>
                    <td>
                        <span class="badge {{ ['pendiente'=>'bg-danger','a_cuenta'=>'bg-warning text-dark','pagado'=>'bg-success'][$p->estado] ?? 'bg-secondary' }}">
                            {{ $p->estadoLabel() }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" title="Ver / registrar abonos" onclick="abrirAbonos({{ $p->id }})"><i class="bi bi-cash-stack"></i></button>
                        <a href="{{ route('pagos.recibo', $p) }}" target="_blank" class="btn btn-sm btn-light btn-icon" title="Estado de cuenta PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        @auth @if(auth()->user()->esAdmin())
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarPago({{ $p->id }})" title="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarPago({{ $p->id }}, '{{ $p->alumno->nombre ?? '' }}')" title="Eliminar"><i class="bi bi-trash"></i></button>
                        @endif @endauth
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No hay pagos registrados para este filtro.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $pagos->links() }}
</div>

@auth @if(auth()->user()->esAdmin())
<!-- MODAL: Registrar / Editar el CARGO (deuda) -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formPago">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title" id="tituloModalPago">Registrar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pago_id">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Alumno</label>
                        <select class="form-select" id="pago_alumno_id" required onchange="cargarTalleresDelAlumno()">
                            <option value="">-- Selecciona --</option>
                            @foreach($alumnos as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Taller</label>
                        <select class="form-select" id="pago_alumno_taller_id">
                            <option value="">-- Selecciona un alumno primero --</option>
                        </select>
                        <small class="text-muted">Un alumno puede tener varios talleres; elige a cual corresponde este pago.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Concepto</label>
                        <input type="text" class="form-control" id="pago_concepto" placeholder="Ej. Mensualidad">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-semibold">Mes</label>
                        <select class="form-select" id="pago_mes">
                            @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small fw-semibold">Ano</label>
                        <input type="number" class="form-control" id="pago_anio" value="{{ $anio }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-semibold">Fecha</label>
                        <input type="date" class="form-control" id="pago_fecha_pago">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Monto total a cobrar (S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="pago_monto_total" required>
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Observacion</label>
                    <textarea class="form-control" id="pago_observacion" rows="2"></textarea>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i> Esto registra el monto que el alumno debe. Los pagos/abonos reales se registran despues, desde el boton <i class="bi bi-cash-stack"></i> de la lista.
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

<!-- MODAL: Ver y registrar ABONOS de un pago (seccion 10) -->
<div class="modal fade" id="modalAbonos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title">Abonos — <span id="abonos_titulo_alumno"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="abonos_pago_id">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><div class="card-kpi card p-2 text-center"><div class="text-muted small">Monto total</div><div class="fw-bold" id="abonos_monto_total">S/ 0.00</div></div></div>
                    <div class="col-md-4"><div class="card-kpi card p-2 text-center"><div class="text-muted small">Abonado</div><div class="fw-bold text-success" id="abonos_abonado">S/ 0.00</div></div></div>
                    <div class="col-md-4"><div class="card-kpi card p-2 text-center"><div class="text-muted small">Saldo</div><div class="fw-bold text-danger" id="abonos_saldo">S/ 0.00</div></div></div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-sm">
                        <thead><tr><th>Fecha</th><th>Metodo</th><th>N° Recibo</th><th>Monto</th><th class="text-end">Recibo</th></tr></thead>
                        <tbody id="abonos_tbody">
                        </tbody>
                    </table>
                </div>

                @auth @if(auth()->user()->esAdmin())
                <hr>
                <h6 class="fw-semibold">Registrar nuevo abono</h6>
                <form id="formAbono" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small">Monto (S/)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="abono_monto" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Fecha</label>
                        <input type="date" class="form-control" id="abono_fecha" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Metodo de pago</label>
                        <select class="form-select" id="abono_metodo" required>
                            <option value="transferencia">Transferencia</option>
                            <option value="yape">Yape</option>
                            <option value="efectivo">Efectivo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">N° Recibo</label>
                        <input type="text" class="form-control" id="abono_recibo_nro">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Observacion (opcional)</label>
                        <input type="text" class="form-control" id="abono_observacion">
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-morado"><i class="bi bi-plus-lg me-1"></i> Registrar abono</button>
                    </div>
                </form>
                @endif @endauth
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modalAbonos = new bootstrap.Modal('#modalAbonos');
    const METODO_LABEL = { transferencia: 'Transferencia', yape: 'Yape', efectivo: 'Efectivo' };
    const ES_ADMIN = @json(auth()->check() && auth()->user()->esAdmin());

    async function abrirAbonos(pagoId) {
        const res = await maFetch(`/pagos/${pagoId}/edit`);
        if (!res) return;
        pintarAbonos(res.data);
        modalAbonos.show();
    }

    function pintarAbonos(pago) {
        document.getElementById('abonos_pago_id').value = pago.id;
        document.getElementById('abonos_titulo_alumno').innerText = pago.alumno ? pago.alumno.nombre : '';
        document.getElementById('abonos_monto_total').innerText = 'S/ ' + Number(pago.monto_total).toFixed(2);
        const abonado = (pago.abonos || []).reduce((s, a) => s + Number(a.monto), 0);
        document.getElementById('abonos_abonado').innerText = 'S/ ' + abonado.toFixed(2);
        document.getElementById('abonos_saldo').innerText = 'S/ ' + Number(pago.saldo).toFixed(2);

        const tbody = document.getElementById('abonos_tbody');
        tbody.innerHTML = '';
        (pago.abonos || []).forEach(a => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${new Date(a.fecha).toLocaleDateString('es-PE')}</td>
                <td>${METODO_LABEL[a.metodo_pago] || a.metodo_pago}</td>
                <td>${a.recibo_nro ?? '—'}</td>
                <td>S/ ${Number(a.monto).toFixed(2)}</td>
                <td class="text-end">
                    <a href="/pagos/abonos/${a.id}/recibo" target="_blank" class="btn btn-sm btn-light btn-icon" title="Recibo PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                    ${ES_ADMIN ? `<button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarAbono(${a.id}, ${pago.id})" title="Corregir/eliminar"><i class="bi bi-trash"></i></button>` : ''}
                </td>`;
            tbody.appendChild(tr);
        });
        if (!(pago.abonos || []).length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-2">Aun no se ha registrado ningun abono.</td></tr>';
        }
    }

    @auth @if(auth()->user()->esAdmin())
    document.getElementById('formAbono').addEventListener('submit', async (e) => {
        e.preventDefault();
        const pagoId = document.getElementById('abonos_pago_id').value;
        const payload = {
            monto: document.getElementById('abono_monto').value,
            fecha: document.getElementById('abono_fecha').value,
            metodo_pago: document.getElementById('abono_metodo').value,
            recibo_nro: document.getElementById('abono_recibo_nro').value,
            observacion: document.getElementById('abono_observacion').value,
        };
        const res = await maFetch(`/pagos/${pagoId}/abonos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            document.getElementById('formAbono').reset();
            document.getElementById('abono_fecha').value = '{{ now()->toDateString() }}';
            pintarAbonos(res.data.pago);
            setTimeout(() => location.reload(), 900);
        }
    });

    async function eliminarAbono(abonoId, pagoId) {
        if (!(await maConfirmarEliminar('este abono'))) return;
        const res = await maFetch(`/pagos/abonos/${abonoId}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            pintarAbonos(res.data);
            setTimeout(() => location.reload(), 900);
        }
    }

    const modalPago = new bootstrap.Modal('#modalPago');

    function nuevoPago() {
        document.getElementById('formPago').reset();
        document.getElementById('pago_id').value = '';
        document.getElementById('pago_alumno_taller_id').innerHTML = '<option value="">-- Selecciona un alumno primero --</option>';
        document.getElementById('tituloModalPago').innerText = 'Registrar Pago';
    }

    async function cargarTalleresDelAlumno(seleccionarId = null) {
        const alumnoId = document.getElementById('pago_alumno_id').value;
        const select = document.getElementById('pago_alumno_taller_id');
        select.innerHTML = '<option value="">Cargando...</option>';
        if (!alumnoId) {
            select.innerHTML = '<option value="">-- Selecciona un alumno primero --</option>';
            return;
        }
        const res = await maFetch(`/alumnos/${alumnoId}/talleres-pago`);
        select.innerHTML = '<option value="">-- Sin taller especifico --</option>';
        if (res && res.ok) {
            res.data.forEach(t => {
                const nombre = (t.especialidad ? t.especialidad.nombre : 'Taller') + (t.maestro ? ' — ' + t.maestro.nombre : '');
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.text = nombre;
                if (seleccionarId && Number(seleccionarId) === t.id) opt.selected = true;
                select.appendChild(opt);
            });
        }
    }

    async function editarPago(id) {
        const res = await maFetch(`/pagos/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('pago_id').value = d.id;
        document.getElementById('pago_alumno_id').value = d.alumno_id;
        await cargarTalleresDelAlumno(d.alumno_taller_id);
        document.getElementById('pago_concepto').value = d.concepto ?? '';
        document.getElementById('pago_mes').value = d.mes;
        document.getElementById('pago_anio').value = d.anio;
        document.getElementById('pago_fecha_pago').value = d.fecha_pago ? d.fecha_pago.substring(0,10) : '';
        document.getElementById('pago_monto_total').value = d.monto_total;
        document.getElementById('pago_observacion').value = d.observacion ?? '';
        document.getElementById('tituloModalPago').innerText = 'Editar Pago';
        modalPago.show();
    }

    document.getElementById('formPago').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('pago_id').value;
        const payload = {
            alumno_id: document.getElementById('pago_alumno_id').value,
            alumno_taller_id: document.getElementById('pago_alumno_taller_id').value || null,
            concepto: document.getElementById('pago_concepto').value,
            mes: document.getElementById('pago_mes').value,
            anio: document.getElementById('pago_anio').value,
            fecha_pago: document.getElementById('pago_fecha_pago').value || null,
            monto_total: document.getElementById('pago_monto_total').value || 0,
            observacion: document.getElementById('pago_observacion').value,
        };
        const url = id ? `/pagos/${id}` : '/pagos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalPago.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarPago(id, nombre) {
        if (!(await maConfirmarEliminar(`el pago de ${nombre}`))) return;
        const res = await maFetch(`/pagos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }
    @endif @endauth
</script>
@endpush
