@extends('layouts.app')
@section('titulo', 'Confirmar acceso')

@section('contenido')
<div class="d-flex justify-content-center">
    <div class="card p-4" style="max-width: 420px; width: 100%;">
        <div class="text-center mb-3">
            <i class="bi bi-shield-lock fs-1" style="color:#3d2c8d"></i>
            <h5 class="fw-semibold mt-2 mb-1">Zona protegida</h5>
            <small class="text-muted">El modulo de Pagos contiene informacion sensible. Confirma tu contrasena para continuar.</small>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first('password') }}</div>
        @endif

        <form method="POST" action="{{ route('pagos.confirmar.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Contrasena</label>
                <input type="password" name="password" class="form-control" autofocus required>
            </div>
            <button type="submit" class="btn btn-morado w-100">Confirmar y continuar</button>
            <a href="{{ route('dashboard') }}" class="btn btn-light w-100 mt-2">Cancelar</a>
        </form>
    </div>
</div>
@endsection
