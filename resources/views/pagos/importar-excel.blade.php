@extends('layouts.app')
@section('titulo', 'Importar Excel de administracion')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Importar ADMINISTRACION_2026.xlsx</h5>
        <small class="text-muted">Sube el Excel de administracion para crear/actualizar alumnos, talleres y pagos de Enero a Setiembre.</small>
    </div>
</div>

<div class="alert alert-warning">
    <i class="bi bi-shield-exclamation me-1"></i>
    Este archivo trae DNI y datos de menores: se procesa y se borra del servidor apenas termina, nunca se guarda en git.
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

@if(! isset($resumenDryRun) && ! isset($resumenFinal))
    <div class="card p-4" style="max-width: 520px;">
        <form method="POST" action="{{ route('pagos.importarExcel.subir') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Archivo Excel (.xlsx)</label>
                <input type="file" name="archivo" accept=".xlsx" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-morado w-100">
                <i class="bi bi-upload me-1"></i> Subir y revisar (sin guardar todavia)
            </button>
        </form>
    </div>
@endif

@if(isset($resumenDryRun))
    <div class="card p-3 mb-3">
        <h6 class="fw-semibold">Resumen de prueba (dry-run) — todavia NO se guardo nada</h6>
        <pre class="small bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $resumenDryRun }}</pre>

        <div class="d-flex gap-2 mt-2">
            <form method="POST" action="{{ route('pagos.importarExcel.confirmar') }}">
                @csrf
                <input type="hidden" name="archivo_temporal" value="{{ $archivoTemporal }}">
                <button type="submit" class="btn btn-success" onclick="return confirm('Esto va a guardar los datos en la base de datos real. ¿Continuar?');">
                    <i class="bi bi-check-lg me-1"></i> Se ve bien, guardar de verdad
                </button>
            </form>
            <a href="{{ route('pagos.importarExcel.form') }}" class="btn btn-light">Cancelar / subir otro archivo</a>
        </div>
    </div>
@endif

@if(isset($resumenFinal))
    <div class="card p-3 mb-3 border-success">
        <h6 class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i>Importacion guardada en la base de datos</h6>
        <pre class="small bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $resumenFinal }}</pre>
        <a href="{{ route('pagos.importarExcel.form') }}" class="btn btn-light mt-2" style="max-width: 260px;">Importar otro archivo</a>
    </div>
@endif
@endsection
