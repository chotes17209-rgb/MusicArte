@extends('layouts.app')
@section('titulo', 'Perfil del alumno')

@section('contenido')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('alumnos.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Alumnos</a>
        <h4 class="fw-bold mb-0 mt-1" style="color:#3d2c8d">{{ $alumno->nombre }}</h4>
        <div class="d-flex gap-2 mt-1 flex-wrap">
            @if($alumno->activo)
                <span class="badge bg-success">Activo</span>
            @else
                <span class="badge bg-secondary">Inactivo</span>
            @endif
            @if($alumno->edad !== null)<span class="badge bg-light text-dark border">{{ $alumno->edad }} años</span>@endif
            @if($alumno->dni)<span class="badge bg-light text-dark border">DNI {{ $alumno->dni }}</span>@endif
        </div>
    </div>
    <button class="btn btn-morado" onclick="editarAlumnoDesdeShow({{ $alumno->id }})"><i class="bi bi-pencil me-1"></i> Editar</button>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-person-vcard me-1"></i> Datos del alumno</h6>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">Tutor</dt><dd class="col-7">{{ $alumno->tutor ?? '—' }}</dd>
                <dt class="col-5 text-muted">Celular</dt><dd class="col-7">{{ $alumno->celular ?? '—' }}</dd>
                <dt class="col-5 text-muted">Fecha nacimiento</dt><dd class="col-7">{{ optional($alumno->fecha_nacimiento)->format('d/m/Y') ?? '—' }}</dd>
                <dt class="col-5 text-muted">Fecha ingreso</dt><dd class="col-7">{{ optional($alumno->fecha_ingreso)->format('d/m/Y') ?? '—' }}</dd>
                @if($alumno->diagnostico)
                <dt class="col-5 text-muted">Diagnostico</dt><dd class="col-7">{{ $alumno->diagnostico }}</dd>
                @endif
                @if($alumno->observaciones)
                <dt class="col-5 text-muted">Observaciones</dt><dd class="col-7">{{ $alumno->observaciones }}</dd>
                @endif
            </dl>
        </div>

        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-music-note-list me-1"></i> Talleres activos ahora</h6>
            @forelse($tallerActual as $t)
                <div class="border rounded p-2 mb-2">
                    <div class="fw-semibold">{{ $t->especialidad->nombre ?? '—' }}</div>
                    <div class="small text-muted">
                        <i class="bi bi-person-badge"></i> {{ $t->maestro->nombre ?? 'Sin maestro asignado' }}
                        @if($t->periodo)<br><i class="bi bi-calendar3"></i> {{ $t->periodo->nombre }}@endif
                        @if($t->salon)<br><i class="bi bi-door-open"></i> Salón {{ $t->salon }}@endif
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">Sin talleres activos actualmente.</p>
            @endforelse
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-3 mb-3">
            <h6 class="fw-semibold mb-1"><i class="bi bi-clock-history me-1"></i> Línea de tiempo por periodo</h6>
            <small class="text-muted d-block mb-3">Con qué maestro, taller y horario estuvo cada mes — util para reincorporarlo con el mismo maestro.</small>

            @forelse($lineaDeTiempo as $item)
                <div class="timeline-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="fw-semibold">{{ $item['periodo']->nombre }}</div>
                        @if($item['estado'] === 'activo')
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
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Taller</th><th>Maestro</th><th>Día</th><th>Hora</th><th>Salón</th><th></th></tr></thead>
                                <tbody>
                                @foreach($item['horarios'] as $h)
                                    <tr class="{{ !$h->activo ? 'text-muted' : '' }}">
                                        <td>{{ $h->especialidad->nombre ?? '—' }}</td>
                                        <td>{{ $h->maestro->nombre ?? '—' }}</td>
                                        <td class="{{ !$h->activo ? 'text-decoration-line-through' : '' }}">{{ $h->diaLabel() }}</td>
                                        <td class="{{ !$h->activo ? 'text-decoration-line-through' : '' }}">{{ \Carbon\Carbon::parse($h->hora_inicio)->format('H:i') }}</td>
                                        <td>{{ $h->salon ?? '—' }}</td>
                                        <td>@if(!$h->activo)<span class="badge bg-light text-muted border">dado de baja</span>@endif</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-muted small mb-0">Este alumno aun no tiene periodos registrados.</p>
            @endforelse
        </div>

        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-cash-coin me-1"></i> Últimos pagos</h6>
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
                <a href="{{ route('pagos.index') }}" class="small d-inline-block mt-2">Ver montos y abonos en Pagos &rarr;</a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editarAlumnoDesdeShow(id) {
        window.location.href = "{{ route('alumnos.index') }}?editar=" + id;
    }
</script>
@endpush
