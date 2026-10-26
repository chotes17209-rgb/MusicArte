@extends('layouts.app')
@section('titulo', 'Reportes')

@section('contenido')
<h5 class="fw-semibold mb-1">Reportes</h5>
<p class="text-muted mb-3">Elige un reporte para verlo o imprimirlo.</p>
<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('reportes.alumnos-especialidad') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-people"></i></div>
                <h6 class="fw-semibold mb-1">Alumnos por Especialidad</h6>
                <small class="text-muted">Distribución de alumnos activos por instrumento/disciplina</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.asistencia-mensual') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-clipboard-check"></i></div>
                <h6 class="fw-semibold mb-1">Asistencia Mensual</h6>
                <small class="text-muted">Porcentaje de asistencia por alumno</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.ingresos-egresos') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-graph-up-arrow"></i></div>
                <h6 class="fw-semibold mb-1">Ingresos vs Egresos</h6>
                <small class="text-muted">Balance financiero mes a mes</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.pagos-pendientes') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-exclamation-triangle"></i></div>
                <h6 class="fw-semibold mb-1">Pagos Pendientes</h6>
                <small class="text-muted">Alumnos con saldo pendiente del mes</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.pagos-anual') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-calendar-range"></i></div>
                <h6 class="fw-semibold mb-1">Historial de Pagos Anual</h6>
                <small class="text-muted">Enero a diciembre: cobrado, a cuenta y deudores</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.planilla-maestros') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-file-earmark-person"></i></div>
                <h6 class="fw-semibold mb-1">Planilla de Maestros</h6>
                <small class="text-muted">Pagos realizados a la plana docente</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.clases') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-calendar3"></i></div>
                <h6 class="fw-semibold mb-1">Clases Dictadas / Canceladas</h6>
                <small class="text-muted">Resumen de clases por rango de fechas</small>
            </div>
        </a>
    </div>
    @php
        $periodosExcel = \App\Models\Periodo::orderByDesc('fecha_inicio')->get();
        $periodoExcel = \App\Models\Periodo::seleccionado();
        $maestrosExcel = \App\Models\Maestro::where('activo', true)->orderBy('nombre')->get();
    @endphp
    <div class="col-md-8">
        <div class="card p-3 h-100">
            <div class="d-flex gap-3 align-items-start">
                <div class="kpi-icon"><i class="bi bi-file-earmark-excel"></i></div>
                <div class="flex-grow-1">
                    <h6 class="fw-semibold mb-1">Lista de asistencia por maestro (Excel)</h6>
                    <small class="text-muted d-block mb-2">Una hoja por maestro con sus alumnos y las fechas de clase del mes, para que lleve la asistencia.</small>
                    <form method="GET" action="{{ route('asistencia.mes.excel') }}" class="row g-2" data-sin-ventana>
                        <div class="col-sm-5">
                            <select name="periodo_id" class="form-select form-select-sm" aria-label="Periodo">
                                @foreach($periodosExcel as $p)
                                    <option value="{{ $p->id }}" @selected($periodoExcel?->id == $p->id)>{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <select name="maestro_id" class="form-select form-select-sm" aria-label="Maestro">
                                <option value="">Todos los maestros</option>
                                @foreach($maestrosExcel as $m)
                                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3 d-grid">
                            <button type="submit" class="btn btn-sm btn-morado"><i class="bi bi-download me-1"></i> Descargar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
