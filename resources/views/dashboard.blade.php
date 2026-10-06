@extends('layouts.app')
@section('titulo', 'Inicio')

@push('estilos')
<style>
    .saludo h4 { font-size: 1.35rem; }
    .hora-grupo + .hora-grupo { border-top: 1px solid #efeeea; }
    .hora-grupo { display: grid; grid-template-columns: 64px 1fr; gap: .75rem; padding: .6rem 0; }
    .hora-grupo .hora { font-size: .8rem; font-weight: 600; color: var(--texto-2); font-variant-numeric: tabular-nums; padding-top: .15rem; }
    .clase-item { display: flex; align-items: center; gap: .55rem; padding: .22rem 0; min-width: 0; }
    .clase-item .color { width: 3px; align-self: stretch; border-radius: 2px; flex-shrink: 0; }
    .clase-item .alumno { font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .clase-item .detalle { color: var(--texto-3); font-size: .78rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .clase-item .estado { margin-left: auto; flex-shrink: 0; }
    .lista-simple { list-style: none; margin: 0; padding: 0; }
    .lista-simple li { display: flex; justify-content: space-between; align-items: center; gap: .5rem; padding: .5rem 0; border-bottom: 1px solid #efeeea; }
    .lista-simple li:last-child { border-bottom: 0; }
    .grafico-meses { display: flex; align-items: flex-end; gap: .75rem; height: 150px; padding-top: 1.25rem; }
    .grafico-meses .col-mes { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; min-width: 0; }
    .grafico-meses .valor { font-size: .78rem; font-weight: 600; margin-bottom: .3rem; font-variant-numeric: tabular-nums; }
    .grafico-meses .barra-v { width: 100%; max-width: 34px; border-radius: 4px 4px 0 0; background: #e8c6e8; }
    .grafico-meses .col-mes:last-child .barra-v { background: var(--acento); }
    .grafico-meses .mes { font-size: .72rem; color: var(--texto-3); margin-top: .4rem; white-space: nowrap; }
    .card-titulo { display: flex; justify-content: space-between; align-items: center; gap: .5rem; margin-bottom: .75rem; }
    .card-titulo h6 { margin: 0; font-size: .875rem; font-weight: 600; }
</style>
@endpush

@section('contenido')
@php
    $h = (int) now()->format('G');
    $saludo = $h < 12 ? 'Buenos días' : ($h < 19 ? 'Buenas tardes' : 'Buenas noches');
    $primerNombre = explode(' ', trim(auth()->user()->name))[0];
@endphp

<div class="page-head saludo">
    <div>
        <h4 class="mb-1">{{ $saludo }}, {{ $primerNombre }}</h4>
        <p class="text-muted mb-0">
            @if($periodoActual) {{ $periodoActual->estaEnCurso() ? 'Periodo en curso' : 'Viendo el periodo' }}: <strong class="text-body fw-medium">{{ $periodoActual->nombre }}</strong> ({{ $periodoActual->fecha_inicio->format('d/m') }} al {{ $periodoActual->fecha_fin->format('d/m') }}) @else No hay un periodo en curso. @endif
        </p>
    </div>
    <div class="page-head-acciones">
        <a href="{{ route('asistencia.index') }}" class="btn btn-light"><i class="bi bi-check2-square me-1"></i> Marcar asistencia</a>
        <a href="{{ route('alumnos.index') }}" class="btn btn-morado"><i class="bi bi-people me-1"></i> Ver alumnos</a>
    </div>
</div>

{{-- Alerta de SUNAT y servicios: faltan 5 dias o menos para el 14, o es el 14. --}}
@if($alertaSunat['mostrar'])
<div class="alert {{ $alertaSunat['es_hoy'] ? 'alert-danger' : 'alert-warning' }} d-flex align-items-center gap-3 mb-3">
    <i class="bi bi-exclamation-triangle fs-5"></i>
    <div>
        <strong>{{ $alertaSunat['es_hoy'] ? 'Hoy es 14:' : 'Faltan '.$alertaSunat['dias_restantes'].' '.($alertaSunat['dias_restantes'] == 1 ? 'día' : 'días').' para el 14:' }}</strong>
        hay que hacer el pago de SUNAT y servicios.
    </div>
</div>
@endif

<div class="stats">
    <x-stat label="Alumnos activos" :valor="$kpis['alumnos_activos']" :detalle="$kpis['maestros_activos'].' maestros activos'" />
    <x-stat label="Clases de hoy" :valor="$kpis['clases_hoy']" :detalle="$kpis['clases_hoy_realizadas'].' ya dictadas'" />
    <x-stat label="Asistencia del mes" :valor="$kpis['asistencia_mes'] !== null ? $kpis['asistencia_mes'].'%' : '—'"
            :tono="$kpis['asistencia_mes'] === null ? null : ($kpis['asistencia_mes'] >= 85 ? 'verde' : ($kpis['asistencia_mes'] >= 65 ? 'ambar' : 'rojo'))"
            detalle="Sobre las clases ya marcadas" />
    <x-stat label="Con pago pendiente" :valor="$kpis['alumnos_con_saldo']" :tono="$kpis['alumnos_con_saldo'] > 0 ? 'ambar' : 'verde'" detalle="Alumnos este mes" />
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <div class="card-titulo">
                <h6>Clases de hoy</h6>
                <a href="{{ route('calendario.index') }}" class="btn btn-sm btn-ir">Abrir calendario <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            @if($clasesHoy->isEmpty())
                <div class="vacio"><i class="bi bi-cup-hot"></i>No hay clases programadas para hoy.</div>
            @else
                @foreach($clasesHoy->groupBy(fn ($c) => \Carbon\Carbon::parse($c->hora_inicio)->format('H:i')) as $hora => $clases)
                    <div class="hora-grupo">
                        <div class="hora">{{ \Carbon\Carbon::createFromFormat('H:i', $hora)->format('g:i a') }}</div>
                        <div class="min-w-0">
                            @foreach($clases as $c)
                                <div class="clase-item">
                                    <span class="color" style="background: {{ $c->especialidad->color ?? '#999' }}"></span>
                                    <div class="min-w-0">
                                        <div class="alumno">{{ $c->alumno->nombre }}</div>
                                        <div class="detalle">{{ $c->especialidad->nombre ?? '—' }} con {{ $c->maestro->nombre ?? '—' }}</div>
                                    </div>
                                    <span class="estado badge {{ $c->estado === 'realizada' ? 'bg-success' : ($c->estado === 'cancelada' ? 'bg-danger' : 'bg-secondary') }}">{{ ['realizada' => 'Dictada', 'cancelada' => 'Cancelada', 'programada' => 'Por dictar'][$c->estado] ?? ucfirst($c->estado) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="col-lg-5 d-flex flex-column gap-3">
        <div class="card p-3">
            <div class="card-titulo">
                <h6>Pago pendiente este mes</h6>
                <a href="{{ route('pagos.index') }}" class="btn btn-sm btn-ir">Ir a Pagos <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            @if($alumnosConSaldo->isEmpty())
                <div class="vacio py-3"><i class="bi bi-check-circle"></i>Todos están al día.</div>
            @else
                <ul class="lista-simple">
                    @foreach($alumnosConSaldo as $a)
                        <li>
                            <a href="{{ route('alumnos.show', $a) }}" class="text-body">{{ $a->nombre }}</a>
                            <span class="badge {{ $a->pagos->contains(fn ($p) => $p->estado === 'a_cuenta') ? 'bg-warning' : 'bg-danger' }}">{{ $a->pagos->contains(fn ($p) => $p->estado === 'a_cuenta') ? 'A cuenta' : 'Sin pagar' }}</span>
                        </li>
                    @endforeach
                </ul>
                @if($kpis['alumnos_con_saldo'] > $alumnosConSaldo->count())
                    <div class="small text-muted mt-2">y {{ $kpis['alumnos_con_saldo'] - $alumnosConSaldo->count() }} más.</div>
                @endif
            @endif
        </div>

        @if($cumpleanieros->isNotEmpty())
        <div class="card p-3">
            <div class="card-titulo"><h6>Cumpleaños del mes</h6></div>
            <ul class="lista-simple">
                @foreach($cumpleanieros as $c)
                    <li>
                        <span>{{ $c->nombre }}</span>
                        <span class="text-muted small {{ \Carbon\Carbon::parse($c->fecha_nacimiento)->day == now()->day ? 'fw-semibold text-body' : '' }}">{{ \Carbon\Carbon::parse($c->fecha_nacimiento)->format('d/m') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="col-12">
        <div class="card p-3">
            <div class="card-titulo">
                <h6>Alumnos activos por mes</h6>
                <a href="{{ route('alumnos.historial') }}" class="btn btn-sm btn-ir">Ver historial <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            @if($alumnosPorMes->isEmpty())
                <div class="vacio">Aún no hay periodos registrados.</div>
            @else
                @php $maxCant = max(1, $alumnosPorMes->max('cantidad')); @endphp
                <div class="grafico-meses">
                    @foreach($alumnosPorMes as $m)
                        <div class="col-mes">
                            <div class="valor">{{ $m['cantidad'] }}</div>
                            <div class="barra-v" style="height: {{ max(3, round($m['cantidad'] / $maxCant * 100)) }}%"></div>
                            <div class="mes" title="{{ $m['label'] }}">{{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before($m['label'], ' '), 3, '') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
