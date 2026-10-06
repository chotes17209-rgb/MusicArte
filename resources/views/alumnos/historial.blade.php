@extends('layouts.app')
@section('titulo', 'Historial de alumnos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Historial de actividad por periodo</h5>
        <small class="text-muted">Qué meses estudió cada alumno. Solo su periodo actual aparece activo: al pasar al siguiente, el anterior queda inactivo.</small>
    </div>
    <a href="{{ route('alumnos.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Alumnos
    </a>
</div>

<div class="card p-3">
    <input type="text" id="filtroHistorial" class="form-control mb-3" placeholder="Buscar alumno por nombre..." autocomplete="off">

    @if($periodos->isEmpty())
        <div class="text-center text-muted py-5">Aún no hay periodos creados. Ve al módulo "Periodos" para crear el primero.</div>
    @else
    <div class="table-responsive">
        <table class="table align-middle table-sm historial" id="tablaHistorial">
            <thead>
                <tr>
                    <th style="min-width:180px">Alumno</th>
                    @foreach($periodos as $p)
                        <th class="text-center" style="min-width:64px" title="{{ $p->nombre }}">{{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before($p->nombre, ' '), 3, '') }}<div class="fw-normal small">{{ $p->anio }}</div></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @forelse($alumnos as $a)
                @php
                    // Solo su periodo mas reciente cuenta como activo; los anteriores
                    // quedan inactivos en su historial (paso al siguiente o termino).
                    $actual = $periodos->reverse()->first(fn ($p) => ($registros[$a->id][$p->id]->estado ?? null) === 'activo');
                    $actualId = $actual && ! $actual->finalizado() ? $actual->id : null;
                @endphp
                <tr class="fila-historial" data-nombre="{{ Str::lower($a->nombre) }}">
                    <td class="fw-semibold"><a href="{{ route('alumnos.show', $a) }}" class="text-decoration-none">{{ $a->nombre }}</a></td>
                    @foreach($periodos as $p)
                        @php $registro = $registros[$a->id][$p->id] ?? null; @endphp
                        <td class="text-center">
                            @if(!$registro)
                                <span class="celda-mes vacia" title="No estuvo matriculado"></span>
                            @elseif($registro->estado === 'activo' && $p->id === $actualId)
                                <span class="celda-mes activa" title="Activo en {{ $p->nombre }}"><i class="bi bi-check-lg"></i></span>
                            @elseif($registro->estado === 'activo')
                                <span class="celda-mes pasada" title="Estudió en {{ $p->nombre }} · ya inactivo"><i class="bi bi-check-lg"></i></span>
                            @else
                                <span class="celda-mes inactiva" title="Inactivo en {{ $p->nombre }}">–</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ $periodos->count() + 1 }}" class="text-center text-muted py-4">No hay alumnos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex flex-wrap gap-3 small text-muted mt-3">
        <span><span class="celda-mes activa"><i class="bi bi-check-lg"></i></span> Activo ahora</span>
        <span><span class="celda-mes pasada"><i class="bi bi-check-lg"></i></span> Estudió ese mes (ya inactivo)</span>
        <span><span class="celda-mes inactiva">–</span> Inscrito pero inactivo</span>
        <span><span class="celda-mes vacia"></span> No estuvo matriculado</span>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // 1.3 Busqueda reactiva: como toda la tabla ya esta cargada, filtramos
    // las filas en el navegador sin ir al servidor.
    document.getElementById('filtroHistorial')?.addEventListener('input', function () {
        const texto = this.value.trim().toLowerCase();
        document.querySelectorAll('#tablaHistorial .fila-historial').forEach(fila => {
            fila.style.display = fila.dataset.nombre.normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes(texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '')) ? '' : 'none';
        });
    });
</script>
@endpush