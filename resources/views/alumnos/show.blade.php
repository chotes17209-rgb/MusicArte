@extends('layouts.app')
@section('titulo', 'Perfil del alumno')

@section('contenido')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('alumnos.index') }}" class="btn btn-sm btn-volver"><i class="bi bi-arrow-left me-1"></i> Volver a Alumnos</a>
        <h4 class="fw-bold mb-0 mt-1">{{ $alumno->nombre }}</h4>
        <div class="d-flex gap-2 mt-1 flex-wrap">
            @if($alumno->activo)
                <span class="badge bg-success">Activo</span>
            @else
                <span class="badge bg-secondary">Inactivo</span>
            @endif
            @if($alumno->edad !== null)<span class="badge bg-light text-dark border">{{ $alumno->edad }}{{ is_numeric(trim($alumno->edad)) ? ' años' : '' }}</span>@endif
            @if($alumno->dni)<span class="badge bg-light text-dark border">DNI {{ $alumno->dni }}</span>@endif
        </div>
    </div>
    {{-- En la ventana "Ver" (pantalla de Alumnos) abre el formulario ahi mismo; en pagina completa va a Alumnos. --}}
    <button class="btn btn-morado" onclick="if (window.editarAlumno) { maCerrarVista(); editarAlumno({{ $alumno->id }}); } else { location.href = '{{ route('alumnos.index') }}?editar={{ $alumno->id }}'; }"><i class="bi bi-pencil me-1"></i> Editar</button>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-3">Datos del alumno</h6>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">Tutor</dt><dd class="col-7">{{ $alumno->tutor ?? '—' }}</dd>
                <dt class="col-5 text-muted">Celular</dt><dd class="col-7">{{ $alumno->celular ?? '—' }}</dd>
                <dt class="col-5 text-muted">Fecha nacimiento</dt><dd class="col-7">{{ optional($alumno->fecha_nacimiento)->format('d/m/Y') ?? '—' }}</dd>
                <dt class="col-5 text-muted">Fecha ingreso</dt><dd class="col-7">{{ optional($alumno->fecha_ingreso)->format('d/m/Y') ?? '—' }}</dd>
                @if($alumno->diagnóstico)
                <dt class="col-5 text-muted">Diagnóstico</dt><dd class="col-7">{{ $alumno->diagnostico }}</dd>
                @endif
                @if($alumno->observaciones)
                <dt class="col-5 text-muted">Observaciones</dt><dd class="col-7">{{ $alumno->observaciones }}</dd>
                @endif
            </dl>
        </div>

        <div class="card p-3">
            <h6 class="fw-semibold mb-3">Talleres de este mes</h6>
            @forelse($tallerActual as $t)
                <div class="border rounded p-2 mb-2">
                    <div class="fw-semibold">{{ $t->especialidad->nombre ?? '—' }}</div>
                    <div class="small text-muted">
                        <i class="bi bi-person-badge"></i> {{ $t->maestro->nombre ?? 'Sin maestro asignado' }}
                        @if($t->horarios->isNotEmpty())
                            @php $dias = $t->horarios->sortBy('dia_semana')->map(fn ($h) => $h->diaLabel())->unique()->values(); @endphp
                            <br><i class="bi bi-clock"></i> {{ $dias->count() > 1 ? $dias->slice(0, -1)->implode(', ').' y '.$dias->last() : $dias->first() }} · {{ \Carbon\Carbon::parse($t->horarios->sortBy('dia_semana')->first()->hora_inicio)->format('g:i a') }}
                        @endif
                        @if($t->periodo)<br><i class="bi bi-calendar3"></i> {{ $t->periodo->nombre }}@endif
                        @if($t->salon)<br><i class="bi bi-door-open"></i> Salón {{ $t->salon }}@endif
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">No tiene talleres en el periodo actual.</p>
            @endforelse
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-1">Línea de tiempo por periodo</h6>
            <small class="text-muted d-block mb-3">Qué talleres llevó cada mes, con qué maestro y en qué días — util para reincorporarlo con el mismo maestro.</small>

            @forelse($lineaDeTiempo as $item)
                <div class="timeline-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        @php $nTalleres = $item['horarios']->where('activo', true)->groupBy(fn ($h) => $h->alumno_taller_id ?? ($h->especialidad_id.'-'.$h->maestro_id))->count(); @endphp
                        <div class="fw-semibold">{{ $item['periodo']->nombre }}@if($nTalleres) <span class="text-muted fw-normal small">· {{ $nTalleres }} {{ $nTalleres === 1 ? 'taller' : 'talleres' }}</span>@endif</div>
                        @if($item['estado'] === 'activo' && $item['paso_a'])
                            <span class="badge bg-secondary">Inactivo · pasó a {{ $item['paso_a'] }}</span>
                        @elseif($item['estado'] === 'activo' && $item['periodo']->finalizado())
                            <span class="badge bg-secondary">Inactivo · periodo finalizado</span>
                        @elseif($item['estado'] === 'activo')
                            <span class="badge bg-success">Activo este periodo</span>
                        @elseif($item['estado'] === 'inactivo')
                            <span class="badge bg-secondary">Inactivo este periodo</span>
                        @else
                            <span class="badge bg-light text-dark border">Sin registro de estado</span>
                        @endif
                    </div>

                    @if($item['horarios']->isEmpty())
                        <p class="text-muted small mb-0">No hay horario detallado registrado para este periodo.</p>
                    @else
                        @php
                            // Una fila por taller con sus dias juntos ("Lunes y Miércoles").
                            // Solo los dias vigentes; si el taller se dio de baja, sus ultimos dias.
                            $porTaller = $item['horarios']->groupBy(fn ($h) => $h->alumno_taller_id ?? ($h->especialidad_id.'-'.$h->maestro_id))
                                ->map(function ($hs) {
                                    $activos = $hs->where('activo', true);
                                    $mostrar = ($activos->isNotEmpty() ? $activos : $hs)->sortBy('dia_semana')->values();
                                    $nombres = $mostrar->map(fn ($h) => $h->diaLabel())->unique()->values();
                                    $horas = $mostrar->map(fn ($h) => \Carbon\Carbon::parse($h->hora_inicio)->format('g:i a'))->unique();

                                    return [
                                        'h' => $mostrar->first(),
                                        'baja' => $activos->isEmpty(),
                                        'dias' => $nombres->count() > 1 ? $nombres->slice(0, -1)->implode(', ').' y '.$nombres->last() : $nombres->first(),
                                        'hora' => $horas->count() === 1
                                            ? $horas->first()
                                            : $mostrar->map(fn ($h) => mb_substr($h->diaLabel(), 0, 3).' '.\Carbon\Carbon::parse($h->hora_inicio)->format('g:i a'))->implode(' · '),
                                        'salon' => $mostrar->pluck('salon')->filter()->unique()->implode(', '),
                                    ];
                                })->sortBy(fn ($t) => $t['h']->especialidad->nombre ?? '');
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Taller</th><th>Maestro</th><th>Días</th><th>Hora</th><th>Salón</th><th></th></tr></thead>
                                <tbody>
                                @foreach($porTaller as $t)
                                    <tr class="{{ $t['baja'] ? 'text-muted' : '' }}">
                                        <td class="fw-semibold">{{ $t['h']->especialidad->nombre ?? '—' }}</td>
                                        <td>{{ $t['h']->maestro->nombre ?? '—' }}</td>
                                        <td>{{ $t['dias'] }}</td>
                                        <td class="text-nowrap">{{ $t['hora'] }}</td>
                                        <td>{{ $t['salon'] ?: '—' }}</td>
                                        <td>@if($t['baja'])<span class="badge bg-light text-muted border">dado de baja</span>@endif</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-muted small mb-0">Este alumno aún no tiene periodos registrados.</p>
            @endforelse
        </div>

        <div class="card p-3">
            <h6 class="fw-semibold mb-3">Últimos pagos</h6>
            @if($pagos->isEmpty())
                <p class="text-muted small mb-0">No hay pagos registrados. (El detalle de montos se gestiona en el módulo de Pagos.)</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Periodo</th><th>Concepto</th><th>Estado</th></tr></thead>
                        <tbody>
                        @foreach($pagos as $p)
                            <tr>
                                <td>{{ $p->mesLabel() }} {{ $p->anio }}</td>
                                <td>{{ $p->concepto ?? 'Mensualidad' }}</td>
                                <td>
                                    <span class="badge {{ ['pendiente'=>'bg-danger','a_cuenta'=>'bg-warning text-dark','pagado'=>'bg-success'][$p->estado] ?? 'bg-secondary' }}">
                                        {{ $p->estadoLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('pagos.index') }}" class="btn btn-sm btn-ir mt-2">Ver montos y abonos en Pagos <i class="bi bi-arrow-right ms-1"></i></a>
            @endif
        </div>
    </div>
</div>
@endsection
