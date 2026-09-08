@extends('layouts.app')
@section('titulo', 'Historial de alumnos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Historial de actividad por periodo</h5>
        <small class="text-muted">En que meses estuvo activo o inactivo cada alumno</small>
    </div>
    <a href="{{ route('alumnos.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Alumnos
    </a>
</div>

<div class="card p-3">
    <input type="text" id="filtroHistorial" class="form-control mb-3" placeholder="Buscar alumno por nombre..." autocomplete="off">

    @if($periodos->isEmpty())
        <div class="text-center text-muted py-5">Aun no hay periodos creados. Ve al modulo "Periodos" para crear el primero.</div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered align-middle table-sm" id="tablaHistorial">
            <thead class="table-light">
                <tr>
                    <th style="min-width:180px">Alumno</th>
                    @foreach($periodos as $p)
                        <th class="text-center" style="min-width:110px">{{ $p->nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @forelse($alumnos as $a)
                <tr class="fila-historial" data-nombre="{{ Str::lower($a->nombre) }}">
                    <td class="fw-semibold"><a href="{{ route('alumnos.show', $a) }}" class="text-decoration-none">{{ $a->nombre }}</a></td>
                    @foreach($periodos as $p)
                        @php $registro = $registros[$a->id][$p->id] ?? null; @endphp
                        <td class="text-center">
                            @if(!$registro)
                                <span class="text-muted">—</span>
                            @elseif($registro->estado === 'activo')
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
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
    <small class="text-muted">"—" significa que el alumno no tenia ningun taller registrado ese periodo (nunca estuvo matriculado ese mes).</small>
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
            fila.style.display = fila.dataset.nombre.includes(texto) ? '' : 'none';
        });
    });
</script>
@endpush