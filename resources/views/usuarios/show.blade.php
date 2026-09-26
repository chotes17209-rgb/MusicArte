@extends('layouts.app')
@section('titulo', 'Usuario')

@section('contenido')
<x-page-head :titulo="$usuario->name" :subtitulo="$usuario->email" :volver="route('usuarios.index')" volver-texto="Usuarios" />

<div class="card p-3">
    <dl class="ficha mb-0">
        <dt>Rol</dt><dd>{{ $usuario->rolLabel() }}</dd>
        <dt>Estado</dt><dd>@if($usuario->activo)<span class="badge bg-success">Puede iniciar sesión</span>@else<span class="badge bg-secondary">Sin acceso</span>@endif</dd>
        <dt>Permisos</dt>
        <dd>{{ $usuario->esAdmin() ? 'Todo el sistema: precios, pagos, planilla, usuarios.' : 'Operación diaria: alumnos, horarios, asistencia, calendario. No cambia precios ni pagos.' }}</dd>
        <dt>Creado</dt><dd>{{ optional($usuario->created_at)->format('d/m/Y H:i') ?? '—' }}</dd>
        <dt>Última actualización</dt><dd>{{ optional($usuario->updated_at)->format('d/m/Y H:i') ?? '—' }}</dd>
    </dl>
</div>
@endsection
