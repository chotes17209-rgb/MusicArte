@extends('layouts.app')
@section('titulo', 'Horarios del mes')

@push('estilos')
<style>
    .dia-badge { display:inline-flex; align-items:center; justify-content:center; min-width:26px; height:24px; padding:0 5px; margin:1px; border-radius:5px; font-size:.72rem; font-weight:600; font-variant-numeric: tabular-nums; }
    .dia-realizada { background:var(--verde-suave); color:var(--verde); }
    .dia-programada { background:var(--acento-suave); color:var(--acento); }
    .dia-cancelada { background:var(--rojo-suave); color:var(--rojo); text-decoration: line-through; }
    .dia-proyectada { background:#f0f0ec; color:var(--texto-2); }
    .semana-col { min-width:118px; }
    .horario-linea { white-space: nowrap; }
    .horario-linea .hora { color: var(--texto-3); margin-left: .35rem; }
</style>
@endpush

@section('contenido')
<div class="page-head">
    <div>
        <a href="{{ route('horarios.index') }}" class="btn btn-sm btn-volver mb-2"><i class="bi bi-arrow-left me-1"></i> Volver a Horarios</a>
        <h4 class="mb-1">Horarios de {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}</h4>
        <p class="text-muted mb-0">Qué días tiene clase cada alumno, semana por semana. {{ count($filas) }} {{ count($filas) === 1 ? 'taller' : 'talleres' }} este mes.</p>
    </div>
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <select name="mes" class="form-select" style="width:150px" onchange="this.form.submit()">
            @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
        </select>
        <input type="number" name="anio" value="{{ $anio }}" class="form-control" style="width:96px" onchange="this.form.submit()">
    </form>
</div>

<div class="card p-3">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <input type="search" id="buscarMensual" class="form-control" style="max-width:300px" placeholder="Buscar alumno o maestro…">
        <div class="d-flex flex-wrap gap-3 small text-muted">
            <span><span class="dia-badge dia-programada">8</span> Programada</span>
            <span><span class="dia-badge dia-realizada">8</span> Realizada</span>
            <span><span class="dia-badge dia-cancelada">8</span> Cancelada</span>
            <span><span class="dia-badge dia-proyectada">8</span> Aún no está en el calendario</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle" id="tablaMensual">
            <thead>
                <tr>
                    <th>Alumno</th><th>Taller</th><th>Horario</th>
                    <th class="semana-col">Semana 1 <span class="fw-normal">(1–7)</span></th>
                    <th class="semana-col">Semana 2 <span class="fw-normal">(8–14)</span></th>
                    <th class="semana-col">Semana 3 <span class="fw-normal">(15–21)</span></th>
                    <th class="semana-col">Semana 4 <span class="fw-normal">(22–fin)</span></th>
                    <th class="text-end">Clases</th>
                </tr>
            </thead>
            <tbody>
            @forelse($filas as $fila)
                <tr data-buscar="{{ mb_strtolower(\Illuminate\Support\Str::ascii(($fila['alumno']->nombre ?? '').' '.($fila['maestro']->nombre ?? ''))) }}">
                    <td class="fw-semibold">{{ $fila['alumno']->nombre ?? '—' }}</td>
                    <td>
                        <div>{{ $fila['especialidad']->nombre ?? '—' }}</div>
                        <div class="small text-muted">{{ $fila['maestro']->nombre ?? 'Sin maestro' }}</div>
                    </td>
                    <td>
                        @foreach($fila['horario'] as $h)
                            <div class="horario-linea">{{ $h['dias'] }}<span class="hora">{{ $h['hora'] }}</span></div>
                        @endforeach
                    </td>
                    @foreach([1,2,3,4] as $s)
                        <td>
                            @forelse($fila['semanas'][$s] as $d)
                                <span class="dia-badge dia-{{ $d['estado'] }}" title="{{ ucfirst($d['estado']) }}">{{ $d['dia'] }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                    @endforeach
                    <td class="text-end fw-semibold">{{ $fila['total'] }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No hay horarios para {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('buscarMensual')?.addEventListener('input', function () {
        const q = this.value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
        document.querySelectorAll('#tablaMensual tbody tr[data-buscar]').forEach(tr => {
            tr.style.display = !q || q.split(/\s+/).every(p => tr.dataset.buscar.includes(p)) ? '' : 'none';
        });
    });
</script>
@endpush
