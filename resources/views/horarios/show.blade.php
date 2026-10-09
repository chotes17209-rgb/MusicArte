@extends('layouts.app')
@section('titulo', 'Horario')

@section('contenido')
<x-page-head :titulo="($horario->alumno->nombre ?? 'Alumno')"
             :subtitulo="($horario->especialidad->nombre ?? '—').' con '.($horario->maestro->nombre ?? '—').' · '.$horario->diaLabel().' '.\Carbon\Carbon::parse($horario->hora_inicio)->format('H:i').' – '.\Carbon\Carbon::parse($horario->hora_fin)->format('H:i')"
             :volver="route('horarios.index')" volver-texto="Horarios" />

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card p-3">
            <dl class="ficha mb-0">
                <dt>Periodo</dt><dd>{{ $horario->periodo->nombre ?? '—' }}</dd>
                <dt>Día</dt><dd>{{ $horario->diaLabel() }}</dd>
                <dt>Hora</dt><dd>{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }} – {{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}</dd>
                <dt>Salón</dt><dd>{{ $horario->salon ?: '—' }}</dd>
                <dt>Estado</dt><dd>@if($horario->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-3">
            <h6 class="seccion-titulo">Clases de este horario ({{ $clases->count() }})</h6>
            @php $nombres = \App\Models\Asistencia::NOMBRES; @endphp
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Fecha</th><th>Clase</th><th>Asistencia</th></tr></thead>
                    <tbody>
                    @forelse($clases as $c)
                        <tr>
                            <td>{{ $c->fecha->format('d/m/Y') }}</td>
                            <td><span class="badge {{ $c->estado === 'realizada' ? 'bg-success' : ($c->estado === 'cancelada' ? 'bg-danger' : 'bg-secondary') }}">{{ ['realizada' => 'Dictada', 'cancelada' => 'Cancelada', 'programada' => 'Por dictar'][$c->estado] ?? $c->estado }}</span></td>
                            <td>{{ $c->asistencia ? ($nombres[$c->asistencia->estado] ?? $c->asistencia->estado) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="vacio">Sin clases generadas.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
