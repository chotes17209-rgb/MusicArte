@extends('layouts.app')
@section('titulo', 'Reportes')

@section('contenido')
<h5 class="fw-semibold mb-3">Centro de Reportes</h5>
<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('reportes.alumnos-especialidad') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <i class="bi bi-people fs-2" style="color:#3d2c8d"></i>
                <h6 class="fw-semibold mt-2 mb-1">Alumnos por Especialidad</h6>
                <small class="text-muted">Distribucion de alumnos activos por instrumento/disciplina</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.asistencia-mensual') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <i class="bi bi-clipboard-check fs-2" style="color:#3d2c8d"></i>
                <h6 class="fw-semibold mt-2 mb-1">Asistencia Mensual</h6>
                <small class="text-muted">Porcentaje de asistencia por alumno</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.ingresos-egresos') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <i class="bi bi-graph-up-arrow fs-2" style="color:#3d2c8d"></i>
                <h6 class="fw-semibold mt-2 mb-1">Ingresos vs Egresos</h6>
                <small class="text-muted">Balance financiero mes a mes</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.pagos-pendientes') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <i class="bi bi-exclamation-triangle fs-2" style="color:#3d2c8d"></i>
                <h6 class="fw-semibold mt-2 mb-1">Pagos Pendientes</h6>
                <small class="text-muted">Alumnos con saldo pendiente del mes</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.planilla-maestros') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <i class="bi bi-file-earmark-person fs-2" style="color:#3d2c8d"></i>
                <h6 class="fw-semibold mt-2 mb-1">Planilla de Maestros</h6>
                <small class="text-muted">Pagos realizados a la plana docente</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('reportes.clases') }}" class="text-decoration-none">
            <div class="card p-3 h-100">
                <i class="bi bi-calendar3 fs-2" style="color:#3d2c8d"></i>
                <h6 class="fw-semibold mt-2 mb-1">Clases Dictadas / Canceladas</h6>
                <small class="text-muted">Resumen de clases por rango de fechas</small>
            </div>
        </a>
    </div>
</div>
@endsection
