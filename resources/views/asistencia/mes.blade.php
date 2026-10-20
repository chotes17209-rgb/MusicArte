@extends('layouts.app')
@section('titulo', 'Asistencia del mes')

@push('estilos')
<style>
    /* Una fila por alumno y taller, sus clases por semana. */
    .tabla-mes { font-size: .82rem; }
    .tabla-mes > :not(caption) > * > * { padding: .3rem .35rem; }
    .tabla-mes th, .tabla-mes td { white-space: nowrap; vertical-align: middle; }
    .tabla-mes thead th { position: sticky; top: 0; background: var(--superficie, #fff); z-index: 2; }
    .tabla-mes .col-alumno { position: sticky; left: 0; background: var(--superficie, #fff); z-index: 1; min-width: 170px; max-width: 200px; white-space: normal; }
    .tabla-mes .col-horario { white-space: normal; min-width: 105px; max-width: 120px; line-height: 1.2; }
    .tabla-mes thead .col-alumno { z-index: 3; }
    .tabla-mes .semana { text-align: center; border-left: 2px solid var(--borde, #e5e5e5); width: 1%; }
    .tabla-mes .semana small { display: block; font-weight: 400; color: #888; }
    .tabla-mes td.semana { white-space: normal; min-width: 80px; max-width: 112px; }
    .tabla-mes tr.grupo td { background: var(--acento-suave, #f7ecf7); font-weight: 700; color: var(--acento); }
    .chip-clase { display: inline-block; min-width: 1.95rem; padding: .25rem .15rem; margin: 1px; border-radius: 5px; border: 1px solid #d0d0d0;
        background: #fff; color: #555; font-weight: 700; font-size: .8rem; text-align: center; cursor: pointer; line-height: 1.1; }
    .chip-clase:hover { outline: 2px solid var(--acento); }
    .chip-clase.asistio, .chip-clase.tardanza { background: #34a853; border-color: #34a853; color: #fff; }
    .chip-clase.falto { background: #e53935; border-color: #e53935; color: #fff; }
    .chip-clase.justificado { background: #fdd835; border-color: #f9c800; color: #333; }
    .chip-clase.recupero { background: var(--acento); border-color: var(--acento); color: #fff; }
    .chip-clase.pendiente { background: #eee; border-color: #bbb; color: #666; }
    .chip-clase.cancelada { background: #fff; border-style: dashed; color: #aaa; text-decoration: line-through; }
    .chip-clase.con-nota::after { content: "•"; margin-left: 2px; }
    .leyenda-mes span { margin-right: 1rem; white-space: nowrap; }
    .leyenda-mes .chip-clase { min-width: 1.2rem; padding: .1rem; cursor: default; }
    .fila-faltas .col-alumno { box-shadow: inset 4px 0 0 #e53935; }
    .btn-estado { min-width: 6.5rem; font-weight: 700; }
</style>
@endpush

@section('contenido')
@php
    $nombres = \App\Models\Asistencia::NOMBRES;
    $totalFaltas = $filas->sum('faltas');
@endphp
<x-page-head titulo="Asistencia del mes" subtitulo="Todas las clases del periodo por alumno y semana. Toca una fecha para marcarla o agregar una nota.">
    <a href="{{ route('asistencia.index') }}" class="btn btn-light"><i class="bi bi-calendar-day me-1"></i> Asistencia del día</a>
</x-page-head>

<div class="card p-3 mb-3">
    <form method="GET" class="row g-2 align-items-center" data-autofiltro>
        <div class="col-md-3">
            <select name="periodo_id" class="form-select" title="Periodo">
                @foreach($periodos as $p)
                    <option value="{{ $p->id }}" @selected($periodo?->id == $p->id)>{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="maestro_id" class="form-select">
                <option value="">Todos los maestros</option>
                @foreach($maestros as $m)
                    <option value="{{ $m->id }}" @selected(request('maestro_id') == $m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="especialidad_id" class="form-select">
                <option value="">Todos los talleres</option>
                @foreach($especialidades as $e)
                    <option value="{{ $e->id }}" @selected(request('especialidad_id') == $e->id)>{{ $e->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar alumno..." autocomplete="off">
        </div>
        <div class="col-md-2">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="con_faltas" name="con_faltas" value="1" @checked(request()->boolean('con_faltas'))>
                <label class="form-check-label" for="con_faltas">Solo con faltas</label>
            </div>
        </div>
    </form>
</div>

<div class="stats">
    <x-stat label="Alumnos / talleres" :valor="$filas->count()" :detalle="$periodo?->nombre" />
    <x-stat label="Asistencias" :valor="$filas->sum('asistio')" tono="verde" detalle="incluye recuperadas" />
    <x-stat label="Faltas" :valor="$totalFaltas" tono="rojo" detalle="para programar recuperación" />
    <x-stat label="Clases pasadas sin marcar" :valor="$filas->sum('sinMarcar')" />
</div>

<div class="card p-3">
    <div class="leyenda-mes small text-muted mb-2">
        <span><span class="chip-clase asistio">&nbsp;</span> Asistió</span>
        <span><span class="chip-clase falto">&nbsp;</span> Faltó</span>
        <span><span class="chip-clase justificado">&nbsp;</span> Faltó con aviso</span>
        <span><span class="chip-clase recupero">&nbsp;</span> Recuperó</span>
        <span><span class="chip-clase pendiente">&nbsp;</span> Pasada sin marcar</span>
        <span><span class="chip-clase">&nbsp;</span> Próxima</span>
        <span>• tiene nota</span>
    </div>
    <div class="table-responsive" style="max-height: 75vh;">
        <table class="table table-sm tabla-mes mb-0">
            <thead>
                <tr>
                    <th class="col-alumno">Alumno</th>
                    <th>Taller</th>
                    <th>Horario</th>
                    @foreach($semanas as $i => $s)
                        <th class="semana">Sem. {{ $i + 1 }}<small>{{ $s['desde']->format('d/m') }}–{{ $s['hasta']->format('d/m') }}</small></th>
                    @endforeach
                    <th class="text-center" title="Asistió / Faltó / Recuperó">A</th>
                    <th class="text-center">F</th>
                    <th class="text-center">R</th>
                </tr>
            </thead>
            <tbody>
            @php $maestroAnterior = false; @endphp
            @forelse($filas as $f)
                @php $t = $f['taller']; $maestroFila = $t->maestro->nombre ?? 'Sin maestro'; @endphp
                @if(! request('maestro_id') && $maestroFila !== $maestroAnterior)
                    <tr class="grupo"><td class="col-alumno" colspan="{{ 6 + count($semanas) }}">Maestro(a) {{ $maestroFila }}</td></tr>
                    @php $maestroAnterior = $maestroFila; @endphp
                @endif
                <tr class="{{ $f['faltas'] > 0 ? 'fila-faltas' : '' }}">
                    <td class="col-alumno fw-semibold">
                        {{ $t->alumno->nombre ?? '—' }}
                        @if($t->alumno?->edadNumero())<span class="text-muted fw-normal small">· {{ $t->alumno->edadNumero() }} años</span>@endif
                        @if($t->estado !== 'activo')<span class="badge bg-secondary ms-1">Baja</span>@endif
                    </td>
                    <td>{{ $t->especialidad->nombre ?? '—' }}@if(request('maestro_id'))<div class="small text-muted">{{ $t->maestro->nombre ?? '' }}</div>@endif</td>
                    <td class="small col-horario">{{ $f['horario'] }}</td>
                    @foreach($semanas as $i => $s)
                        <td class="semana">
                            @foreach($f['porSemana']->get($i, collect()) as $c)
                                @php
                                    $estado = $c->asistencia->estado ?? null;
                                    $clase = $estado ?? ($c->estado === 'cancelada' ? 'cancelada' : ($c->fecha->lt(today()) ? 'pendiente' : ''));
                                    $titulo = $c->fecha->translatedFormat('l d/m').' '.\Carbon\Carbon::parse($c->hora_inicio)->format('g:i a')
                                        .' — '.($estado ? ($nombres[$estado] ?? $estado) : ($c->estado === 'cancelada' ? 'Cancelada' : 'Sin marcar'))
                                        .($c->asistencia?->observacion ? ' · '.$c->asistencia->observacion : '');
                                @endphp
                                <button type="button" class="chip-clase {{ $clase }} {{ $c->asistencia?->observacion ? 'con-nota' : '' }}"
                                        id="chip-{{ $c->id }}" title="{{ $titulo }}"
                                        data-clase="{{ $c->id }}" data-alumno="{{ $t->alumno->nombre ?? '' }}" data-taller="{{ $t->especialidad->nombre ?? '' }}"
                                        data-fecha="{{ $c->fecha->translatedFormat('l d/m') }} · {{ \Carbon\Carbon::parse($c->hora_inicio)->format('g:i a') }}"
                                        data-estado="{{ $estado }}" data-observacion="{{ $c->asistencia?->observacion }}"
                                        onclick="abrirClase(this)">{{ $c->fecha->format('d') }}</button>
                            @endforeach
                        </td>
                    @endforeach
                    <td class="text-center text-success fw-semibold">{{ $f['asistio'] }}</td>
                    <td class="text-center fw-semibold {{ $f['faltas'] ? 'text-danger' : 'text-muted' }}">{{ $f['faltas'] }}</td>
                    <td class="text-center fw-semibold {{ $f['recupero'] ? '' : 'text-muted' }}" style="{{ $f['recupero'] ? 'color: var(--acento)' : '' }}">{{ $f['recupero'] }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ 6 + count($semanas) }}"><div class="vacio"><i class="bi bi-calendar-x"></i>No hay alumnos para este filtro.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: marcar una clase desde la vista del mes -->
<div class="modal fade" id="modalClaseMes" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="mesTitulo"></h5>
                    <div class="small text-muted" id="mesSubtitulo"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mesClaseId">
                <label class="form-label small fw-semibold">Observación (opcional)</label>
                <textarea class="form-control mb-3" id="mesObservacion" rows="2" maxlength="1000" placeholder="Ej.: se programó su recuperación para el sábado..."></textarea>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <button type="button" class="btn btn-success btn-estado" onclick="guardarClase('asistio')">A · Asistió</button>
                    <button type="button" class="btn btn-danger btn-estado" onclick="guardarClase('falto')">F · Faltó</button>
                    <button type="button" class="btn btn-warning btn-estado" onclick="guardarClase('justificado')">Faltó con aviso</button>
                    <button type="button" class="btn btn-morado btn-estado" onclick="guardarClase('recupero')">R · Recuperó</button>
                    <button type="button" class="btn btn-light btn-estado" onclick="guardarClase('sin_marcar')">S · Sin marcar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modalClaseMes = new bootstrap.Modal('#modalClaseMes');
    const NOMBRES_ESTADO = @json($nombres);

    function abrirClase(chip) {
        document.getElementById('mesClaseId').value = chip.dataset.clase;
        document.getElementById('mesTitulo').textContent = chip.dataset.alumno;
        const actual = chip.dataset.estado ? ' — ahora: ' + (NOMBRES_ESTADO[chip.dataset.estado] || chip.dataset.estado) : ' — sin marcar';
        document.getElementById('mesSubtitulo').textContent = `${chip.dataset.taller} · ${chip.dataset.fecha}${actual}`;
        document.getElementById('mesObservacion').value = chip.dataset.observacion || '';
        modalClaseMes.show();
    }

    async function guardarClase(estado) {
        const id = document.getElementById('mesClaseId').value;
        const observacion = document.getElementById('mesObservacion').value.trim();
        const res = await maFetch(`/asistencia/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(estado === 'sin_marcar' ? { estado } : { estado, observacion }),
        });
        if (res && res.ok) {
            modalClaseMes.hide();
            maToast('success', estado === 'sin_marcar' ? 'Quedó sin marcar' : 'Marcado: ' + (NOMBRES_ESTADO[estado] || estado));
            // Se recarga para actualizar colores y totales, manteniendo la posicion.
            try { sessionStorage.setItem('ma_mes_scroll', JSON.stringify({ y: window.scrollY, x: document.querySelector('.tabla-mes').parentElement.scrollLeft, t: document.querySelector('.tabla-mes').parentElement.scrollTop })); } catch (e) {}
            setTimeout(() => location.reload(), 500);
        }
    }

    try {
        const pos = JSON.parse(sessionStorage.getItem('ma_mes_scroll') || 'null');
        sessionStorage.removeItem('ma_mes_scroll');
        if (pos) {
            const cont = document.querySelector('.tabla-mes').parentElement;
            cont.scrollLeft = pos.x; cont.scrollTop = pos.t; window.scrollTo(0, pos.y);
        }
    } catch (e) {}
</script>
@endpush
