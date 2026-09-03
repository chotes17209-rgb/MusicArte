<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Nombre</th><th>Edad</th><th>Talleres</th><th>Maestro(s)</th><th>Tutor</th><th>Celular</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
        @forelse($alumnos as $a)
            <tr>
                <td class="fw-semibold">{{ $a->nombre }}</td>
                <td>{{ $a->edad !== null ? $a->edad.' años' : '—' }}</td>
                <td>{{ $a->talleresLabel() }}</td>
                <td>{{ $a->maestrosLabel() }}</td>
                <td>{{ $a->tutor ?? '—' }}</td>
                <td>{{ $a->celular ?? '—' }}</td>
                <td>@if($a->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-light btn-icon" onclick="editarAlumno({{ $a->id }})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarAlumno({{ $a->id }}, '{{ $a->nombre }}')"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron alumnos con los filtros aplicados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-2">{{ $alumnos->links() }}</div>
