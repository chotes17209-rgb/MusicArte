@extends('layouts.app')
@section('titulo', 'Horarios - Vista Mensual')

@push('estilos')
<style>
    .dia-badge { display:inline-block; min-width:26px; padding:2px 5px; margin:1px; border-radius:5px; font-size:.72rem; font-weight:600; text-align:center; }
    .dia-realizada { background:#c8e6c9; color:#1b5e20; }
    .dia-programada { background:#fff3cd; color:#7a5b00; }
    .dia-cancelada { background:#f8d7da; color:#842029; }
    .dia-proyectada { background:#e9ecef; color:#495057; }
    .semana-col { min-width:140px; }
</style>
@endpush

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('horarios.index') }}" class="text-muted small"><i class="bi bi-arrow-left"></i> Volver a Horarios</a>
        <h5 class="fw-semibold mb-0 mt-1">Horarios por Alumno — {{ \App\Models\Pago::MESES[$mes] }} {{ $anio }}</h5>
        <small class="text-muted">Cada mes dividido en 4 semanas</small>
    </div>
    <form class="d-flex gap-2" method="GET">
        <select name="mes" class="form-select form-select-sm">
            @foreach(\App\Models\Pago::MESES as $num => $nombre)<option value="{{ $num }}" @selected($mes==$num)>{{ $nombre }}</option>@endforeach
        </select>
        <input type="number" name="anio" value="{{ $anio }}" class="form-control form-control-sm" style="width:90px">
        <button class="btn btn-sm btn-light">Filtrar</button>
    </form>
</div>

<div class="d-flex gap-3 small mb-3">
    <span><span class="dia-badge dia-realizada">15</span> Realizada</span>
    <span><span class="dia-badge dia-programada">15</span> Programada</span>
    <span><span class="dia-badge dia-cancelada">15</span> Cancelada</span>
    <span><span class="dia-badge dia-proyectada">15</span> Proyectada (aun no generada en el calendario)</span>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Alumno</th><th>Especialidad</th><th>Maestro</th><th>Horario</th>
                    <th class="semana-col">Semana 1<br><small class="text-muted">(1-7)</small></th>
                    <th class="semana-col">Semana 2<br><small class="text-muted">(8-14)</small></th>
                    <th class="semana-col">Semana 3<br><small class="text-muted">(15-21)</small></th>
                    <th class="semana-col">Semana 4<br><small class="text-muted">(22-fin)</small></th>
                </tr>
            </thead>
            <tbody>
            @forelse($filas as $fila)
                <tr>
                    <td class="fw-semibold">{{ $fila['alumno']->nombre ?? '—' }}</td>
                    <td>{{ $fila['especialidad']->nombre ?? '—' }}</td>
                    <td>{{ $fila['maestro']->nombre ?? '—' }}</td>
                    <td class="small">{{ $fila['horario_texto'] }}</td>
                    @foreach([1,2,3,4] as $s)
                        <td>
                            @forelse($fila['semanas'][$s] as $d)
                                <span class="dia-badge dia-{{ $d['estado'] }}">{{ $d['dia'] }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No hay horarios activos configurados. Ve a "Nuevo Horario" para agregar alumnos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
