@extends('layouts.app')
@section('titulo', 'Dashboard')

@section('contenido')
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-kpi p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Alumnos activos</div>
                    <div class="fs-3 fw-bold" style="color:#3d2c8d">{{ $kpis['alumnos_activos'] }}</div>
                </div>
                <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-people fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-kpi p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Clases hoy</div>
                    <div class="fs-3 fw-bold" style="color:#3d2c8d">{{ $kpis['clases_hoy'] }}</div>
                    <div class="small text-success">{{ $kpis['clases_hoy_realizadas'] }} realizadas</div>
                </div>
                <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-calendar3 fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-kpi p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Ingresos del mes</div>
                    <div class="fs-4 fw-bold text-success">S/ {{ number_format($kpis['ingresos_mes'], 2) }}</div>
                </div>
                <div class="btn-icon" style="background:#e8f7ee;color:#198754"><i class="bi bi-cash-coin fs-5"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-kpi p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">Egresos del mes</div>
                    <div class="fs-4 fw-bold text-danger">S/ {{ number_format($kpis['egresos_mes'], 2) }}</div>
                </div>
                <div class="btn-icon" style="background:#fdecea;color:#dc3545"><i class="bi bi-receipt fs-5"></i></div>
            </div>
        </div>
    </div>
</div>

@if($kpis['pagos_pendientes'] > 0)
<div class="alert alert-warning d-flex align-items-center gap-2 shadow-sm">
    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
    <div>
        <strong>{{ $kpis['pagos_pendientes'] }} alumno(s)</strong> tienen saldo pendiente este mes, por un total de
        <strong>S/ {{ number_format($kpis['saldo_pendiente_total'], 2) }}</strong>.
        <a href="{{ route('pagos.index') }}" class="ms-2">Ver pagos &rarr;</a>
    </div>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <h6 class="fw-semibold mb-3"><i class="bi bi-calendar-event me-1"></i> Clases de hoy</h6>
            @if($clasesHoy->isEmpty())
                <p class="text-muted small mb-0">No hay clases programadas para hoy.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Hora</th><th>Alumno</th><th>Maestro</th><th>Especialidad</th><th>Estado</th></tr></thead>
                        <tbody>
                        @foreach($clasesHoy as $c)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($c->hora_inicio)->format('H:i') }}</td>
                                <td>{{ $c->alumno->nombre }}</td>
                                <td>{{ $c->maestro->nombre ?? '—' }}</td>
                                <td>{{ $c->especialidad->nombre ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $c->estado === 'realizada' ? 'bg-success' : ($c->estado === 'cancelada' ? 'bg-danger' : 'bg-secondary') }}">{{ ucfirst($c->estado) }}</span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <a href="{{ route('calendario.index') }}" class="small mt-2">Ir al calendario completo &rarr;</a>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-cash-stack me-1"></i> Saldos pendientes (mes actual)</h6>
            @if($alumnosConSaldo->isEmpty())
                <p class="text-muted small mb-0">No hay saldos pendientes registrados.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($alumnosConSaldo as $a)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ $a->nombre }}</span>
                            <span class="text-danger fw-semibold">S/ {{ number_format($a->pagos->first()->saldo ?? 0, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if($cumpleanieros->isNotEmpty())
        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-balloon-heart-fill me-1"></i> Cumpleanos del mes</h6>
            <ul class="list-group list-group-flush">
                @foreach($cumpleanieros as $c)
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span>{{ $c->nombre }}</span>
                        <span class="text-muted small">{{ \Carbon\Carbon::parse($c->fecha_nacimiento)->format('d/m') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
