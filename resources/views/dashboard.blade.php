@extends('layouts.app')
@section('titulo', 'Dashboard')

@section('contenido')

{{-- 20. Alerta de SUNAT y servicios: siempre visible por encima de todo
     cuando faltan 5 dias o menos para el 14, o cuando es el dia 14. --}}
@if($alertaSunat['mostrar'])
<div class="alert {{ $alertaSunat['es_hoy'] ? 'alert-danger' : 'alert-warning' }} d-flex align-items-center gap-2 shadow-sm mb-4" style="border-left: 6px solid {{ $alertaSunat['es_hoy'] ? '#dc3545' : '#c99a06' }};">
    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
    <div>
        @if($alertaSunat['es_hoy'])
            <div class="fw-bold fs-5">⚠️ HOY ES 14</div>
        @else
            <div class="fw-bold fs-5">⚠️ FALTAN {{ $alertaSunat['dias_restantes'] }} {{ $alertaSunat['dias_restantes'] == 1 ? 'DIA' : 'DIAS' }} PARA EL 14</div>
        @endif
        <div>Se debe realizar el pago de SUNAT y servicios.</div>
    </div>
</div>
@endif

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
                    <div class="text-muted small">Maestros activos</div>
                    <div class="fs-3 fw-bold" style="color:#3d2c8d">{{ $kpis['maestros_activos'] }}</div>
                </div>
                <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-person-badge fs-5"></i></div>
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
        <a href="{{ route('pagos.index') }}" class="text-decoration-none">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Pagos</div>
                        <div class="fs-6 fw-semibold" style="color:#3d2c8d">Ver modulo &rarr;</div>
                        <div class="small text-muted">Informacion financiera aqui</div>
                    </div>
                    <div class="btn-icon" style="background:#eee9fb;color:#3d2c8d"><i class="bi bi-cash-coin fs-5"></i></div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 19.1: alumnos por periodo/mes --}}
<div class="card p-3 mb-4">
    <h6 class="fw-semibold mb-3"><i class="bi bi-bar-chart-line me-1"></i> Alumnos por mes</h6>
    @if($alumnosPorMes->isEmpty())
        <p class="text-muted small mb-0">Aun no hay periodos registrados.</p>
    @else
        @php $maxCant = max(1, $alumnosPorMes->max('cantidad')); @endphp
        <div class="d-flex align-items-end gap-3" style="height:140px;">
            @foreach($alumnosPorMes as $m)
                <div class="d-flex flex-column align-items-center justify-content-end" style="flex:1; height:100%;">
                    <div class="small fw-semibold mb-1">{{ $m['cantidad'] }}</div>
                    <div style="width:100%; max-width:48px; background:#3d2c8d; border-radius:6px 6px 0 0; height:{{ max(4, round($m['cantidad'] / $maxCant * 100)) }}%;"></div>
                    <div class="small text-muted mt-1 text-center">{{ $m['label'] }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>

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
            <h6 class="fw-semibold mb-3"><i class="bi bi-cash-stack me-1"></i> Alumnos con pago pendiente (mes actual)</h6>
            @if($alumnosConSaldo->isEmpty())
                <p class="text-muted small mb-0">No hay pagos pendientes registrados.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($alumnosConSaldo as $a)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ $a->nombre }}</span>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('pagos.index') }}" class="small mt-2">Ver detalle de montos en Pagos &rarr;</a>
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
