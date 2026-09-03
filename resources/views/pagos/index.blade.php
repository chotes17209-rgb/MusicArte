@extends('layouts.app')
@section('titulo', 'Pagos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Pagos / Mensualidades</h5>
        <small class="text-muted">
            @if($periodos->firstWhere('id', $periodoId))
                {{ $periodos->firstWhere('id', $periodoId)->nombre }}
            @else
                Todos los periodos
            @endif
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pagos.anual') }}" class="btn btn-outline-secondary"><i class="bi bi-calendar-range me-1"></i>Historial anual</a>
        @auth @if(auth()->user()->esAdmin())
        <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalPago" onclick="nuevoPago()"><i class="bi bi-plus-lg me-1"></i> Registrar Pago</button>
        @else
        <span class="badge bg-secondary align-self-center"><i class="bi bi-lock-fill me-1"></i>Modo solo lectura (precios reservados al administrador)</span>
        @endif @endauth
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Facturado</div><div class="fs-5 fw-bold">S/ {{ number_format($totales['facturado'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Recaudado</div><div class="fs-5 fw-bold text-success">S/ {{ number_format($totales['recaudado'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3 card-kpi"><div class="text-muted small">Pendiente de cobro</div><div class="fs-5 fw-bold text-danger">S/ {{ number_format($totales['pendiente'], 2) }}</div></div></div>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-3">
            <select name="periodo_id" class="form-select">
                <option value="">Todos los periodos</option>
                @foreach($periodos as $p)
                    <option value="{{ $p->id }}" @selected($periodoId == $p->id)>{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="maestro_id" class="form-select">
                <option value="">Todos los maestros</option>
                @foreach($maestros as $m)
                    <option value="{{ $m->id }}" @selected(request('maestro_id') == $m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="especialidad_id" class="form-select">
                <option value="">Todos los talleres</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp->id }}" @selected(request('especialidad_id') == $esp->id)>{{ $esp->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\Pago::ESTADOS as $key => $label)
                    <option value="{{ $key }}" @selected(request('estado') == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar alumno...">
        </div>
        <div class="col-md-1"><button class="btn btn-light w-100">Filtrar</button></div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Alumno</th><th>Taller</th><th>Periodo</th><th>Monto</th><th>Pagado</th><th>Saldo</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($pagos as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->alumno->nombre ?? '—' }}</td>
                    <td>{{ $p->especialidad->nombre ?? '—' }}</td>
                    <td>{{ $p->periodo->nombre ?? ($p->mesLabel().' '.$p->anio) }}</td>
                    <td>S/ {{ number_format($p->monto_total, 2) }}</td>
                    <td>S/ {{ number_format($p->monto_pagado, 2) }}</td>
                    <td>S/ {{ number_format($p->saldo, 2) }}</td>
                    <td>
                        @if($p->estado === 'pagado')<span class="badge bg-success">Pagado</span>
                        @elseif($p->estado === 'a_cuenta')<span class="badge bg-warning text-dark">A cuenta</span>
                        @else<span class="badge bg-danger">Pendiente</span>@endif
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light btn-icon" title="Ver abonos" onclick="verAbonos({{ $p->id }})"><i class="bi bi-cash-coin"></i></button>
                        <a href="{{ route('pagos.recibo', $p) }}" target="_blank" class="btn btn-sm btn-light btn-icon" title="Resumen PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        @auth @if(auth()->user()->esAdmin())
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarPago({{ $p->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarPago({{ $p->id }}, '{{ $p->alumno->nombre ?? '' }}')"><i class="bi bi-trash"></i></button>
                        @endif @endauth
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No hay pagos registrados con estos filtros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $pagos->links() }}
</div>

<!-- Modal: ver / registrar abonos de un pago -->
<div class="modal fade" id="modalAbonos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#3d2c8d;color:#fff">
                <h5 class="modal-title">Abonos de <span id="abonos_alumno_nombre"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="abonos_pago_id">
                <div class="row text-center mb-3">
                    <div class="col-4"><div class="text-muted small">Total</div><div class="fw-bold" id="abonos_total">—</div></div>
                    <div class="col-4"><div class="text-muted small">Pagado</div><div class="fw-bold text-success" id="abonos_pagado">—</div></div>
                    <div class="col-4"><div class="text-muted small">Saldo</div><div class="fw-bold text-danger" id="abonos_saldo">—</div></div>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Fecha</th><th>Metodo</th><th>Recibo</th><th>Monto</th><th></th></tr></thead>
                        <tbody id="abonos_tabla"></tbody>
                    </table>
                </div>
                @auth @if(auth()->user()->esAdmin())
                <hr>
                <h6 class="small fw-semibold text-uppercase text-muted">Registrar nuevo abono</h6>
                <form id="formAbono" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small">Monto (S/)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="abono_monto" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Fecha</label>
                        <input type="date" class="form-control" id="abono_fecha" required value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Metodo</label>
                        <select class="form-select" id="abono_metodo" required>
                            @foreach(\App\Models\PagoAbono::METODOS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">N° Recibo</label>
                        <input type="text" class="form-control" id="abono_recibo">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-morado w-100 mt-2"><i class="bi bi-plus-lg me-1"></i>Agregar abono</button>
                    </div>
                </form>
                @endif @endauth
            </div>
        </div>
    </div>
</div>

@auth @if(auth()->user()->esAdmin())
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
                        <select class="form-select" id="pago_alumno_id" required>
                            <option value="">-- Selecciona --</option>
                            @foreach($alumnos as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Concepto</label>
                        <input type="text" class="form-control" id="pago_concepto" placeholder="Ej. Mensualidad Piano">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Taller</label>
                        <select class="form-select" id="pago_especialidad_id">
                            <option value="">-- Selecciona --</option>
                            @foreach($especialidades as $esp)<option value="{{ $esp->id }}">{{ $esp->nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Maestro</label>
                        <select class="form-select" id="pago_maestro_id">
                            <option value="">-- Selecciona --</option>
                            @foreach($maestros as $m)<option value="{{ $m->id }}">{{ $m->nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Periodo</label>
                        <select class="form-select" id="pago_periodo_id" required>
                            <option value="">-- Selecciona --</option>
                            @foreach($periodos as $p)<option value="{{ $p->id }}" @selected($periodoId==$p->id)>{{ $p->nombre }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Monto total (S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="pago_monto_total" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Observacion</label>
                    <textarea class="form-control" id="pago_observacion" rows="2"></textarea>
                </div>

                <div id="bloquePrimerAbono">
                    <hr>
                    <h6 class="small fw-semibold text-uppercase text-muted">Primer abono (opcional)</h6>
                    <small class="text-muted d-block mb-2">Si el alumno ya pago algo al momento de registrar, complétalo aqui. Si no, deja el monto en blanco y agrega el abono despues desde "Ver abonos".</small>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label small">Monto (S/)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="abono0_monto">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label small">Fecha</label>
                            <input type="date" class="form-control" id="abono0_fecha" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label small">Metodo</label>
                            <select class="form-select" id="abono0_metodo">
                                @foreach(\App\Models\PagoAbono::METODOS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label small">N° Recibo</label>
                            <input type="text" class="form-control" id="abono0_recibo">
                        </div>
                    </div>
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
    const modalAbonos = new bootstrap.Modal('#modalAbonos');
    const esAdmin = {{ (auth()->check() && auth()->user()->esAdmin()) ? 'true' : 'false' }};

    function pintarAbonos(pago) {
        document.getElementById('abonos_pago_id').value = pago.id;
        document.getElementById('abonos_alumno_nombre').innerText = pago.alumno?.nombre ?? '';
        document.getElementById('abonos_total').innerText = 'S/ ' + Number(pago.monto_total).toFixed(2);
        document.getElementById('abonos_pagado').innerText = 'S/ ' + Number(pago.monto_pagado).toFixed(2);
        document.getElementById('abonos_saldo').innerText = 'S/ ' + Number(pago.saldo).toFixed(2);

        const tbody = document.getElementById('abonos_tabla');
        tbody.innerHTML = '';
        (pago.abonos || []).forEach(a => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${(a.fecha || '').substring(0,10)}</td>
                <td>${a.metodo_pago}</td>
                <td>${a.recibo_nro ?? '—'}</td>
                <td>S/ ${Number(a.monto).toFixed(2)}</td>
                <td class="text-end">
                    <a href="/pagos/${pago.id}/abonos/${a.id}/recibo" target="_blank" class="btn btn-sm btn-light btn-icon" title="Recibo"><i class="bi bi-file-earmark-pdf"></i></a>
                    ${esAdmin ? `<button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarAbono(${pago.id}, ${a.id})"><i class="bi bi-trash"></i></button>` : ''}
                </td>`;
            tbody.appendChild(tr);
        });
        if (!(pago.abonos || []).length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Aun no hay abonos registrados.</td></tr>';
        }
    }

    async function verAbonos(pagoId) {
        const res = await maFetch(`/pagos/${pagoId}/edit`);
        if (!res) return;
        pintarAbonos(res.data);
        modalAbonos.show();
    }

    const formAbono = document.getElementById('formAbono');
    if (formAbono) {
        formAbono.addEventListener('submit', async (e) => {
            e.preventDefault();
            const pagoId = document.getElementById('abonos_pago_id').value;
            const payload = {
                monto: document.getElementById('abono_monto').value,
                fecha: document.getElementById('abono_fecha').value,
                metodo_pago: document.getElementById('abono_metodo').value,
                recibo_nro: document.getElementById('abono_recibo').value,
            };
            const res = await maFetch(`/pagos/${pagoId}/abonos`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (res && res.ok) {
                maToast('success', res.message);
                formAbono.reset();
                document.getElementById('abono_fecha').value = new Date().toISOString().substring(0, 10);
                pintarAbonos(res.data);
                setTimeout(() => location.reload(), 900);
            }
        });
    }

    async function eliminarAbono(pagoId, abonoId) {
        if (!(await maConfirmarEliminar('este abono'))) return;
        const res = await maFetch(`/pagos/${pagoId}/abonos/${abonoId}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            pintarAbonos(res.data);
            setTimeout(() => location.reload(), 900);
        }
    }

@auth @if(auth()->user()->esAdmin())
    const modalPago = new bootstrap.Modal('#modalPago');

    function nuevoPago() {
        document.getElementById('formPago').reset();
        document.getElementById('pago_id').value = '';
        document.getElementById('bloquePrimerAbono').style.display = 'block';
        document.getElementById('tituloModalPago').innerText = 'Registrar Pago';
    }

    async function editarPago(id) {
        const res = await maFetch(`/pagos/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('pago_id').value = d.id;
        document.getElementById('pago_alumno_id').value = d.alumno_id;
        document.getElementById('pago_concepto').value = d.concepto ?? '';
        document.getElementById('pago_especialidad_id').value = d.especialidad_id ?? '';
        document.getElementById('pago_maestro_id').value = d.maestro_id ?? '';
        document.getElementById('pago_periodo_id').value = d.periodo_id ?? '';
        document.getElementById('pago_monto_total').value = d.monto_total;
        document.getElementById('pago_observacion').value = d.observacion ?? '';
        document.getElementById('bloquePrimerAbono').style.display = 'none';
        document.getElementById('tituloModalPago').innerText = 'Editar Pago';
        modalPago.show();
    }

    document.getElementById('formPago').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('pago_id').value;
        const payload = {
            alumno_id: document.getElementById('pago_alumno_id').value,
            especialidad_id: document.getElementById('pago_especialidad_id').value || null,
            maestro_id: document.getElementById('pago_maestro_id').value || null,
            periodo_id: document.getElementById('pago_periodo_id').value,
            concepto: document.getElementById('pago_concepto').value,
            monto_total: document.getElementById('pago_monto_total').value || 0,
            observacion: document.getElementById('pago_observacion').value,
        };

        if (!id) {
            const monto0 = document.getElementById('abono0_monto').value;
            if (monto0 && Number(monto0) > 0) {
                payload.primer_abono = {
                    monto: monto0,
                    fecha: document.getElementById('abono0_fecha').value,
                    metodo_pago: document.getElementById('abono0_metodo').value,
                    recibo_nro: document.getElementById('abono0_recibo').value,
                };
            }
        }

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
