@extends('layouts.app')
@section('titulo', 'Confirmar acceso')

@section('contenido')
<div class="d-flex justify-content-center pt-4">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
        <div class="kpi-icon mb-3"><i class="bi bi-lock"></i></div>
        <h5 class="mb-1">Confirma tu contraseña</h5>
        <p class="text-muted mb-3">Pagos tiene información sensible. Vuelve a escribir tu contraseña para continuar.</p>

        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first('password') }}</div>
        @endif

        <form method="POST" action="{{ route('pagos.confirmar.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" autofocus required autocomplete="current-password">
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-light flex-fill">Cancelar</a>
                <button type="submit" class="btn btn-morado flex-fill">Continuar</button>
            </div>
        </form>
    </div>
</div>
@endsection
