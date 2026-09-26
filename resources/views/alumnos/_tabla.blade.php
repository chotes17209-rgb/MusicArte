<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Taller</th>
                <th>Contacto (tutor)</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @forelse($alumnos as $a)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $a->nombre }}</div>
                    @if($a->edad)<div class="small text-muted">{{ $a->edad }}</div>@endif
                </td>
                <td>
                    @if($a->especialidad)
                        <div>{{ $a->especialidad->nombre }} <span class="text-muted">con</span> {{ $a->maestro->nombre ?? '—' }}</div>
                    @else
                        <span class="text-muted">Sin taller</span>
                    @endif
                    @if($a->talleres_activos_count > 1)
                        <div class="small text-muted">{{ $a->talleres_activos_count }} talleres en total</div>
                    @endif
                </td>
                <td>
                    <div>{{ $a->tutor ?? '—' }}</div>
                    @if($a->celular)<div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $a->celular }}</div>@endif
                </td>
                <td>
                    @if($a->activo)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-secondary">Inactivo</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('alumnos.show', $a) }}" class="btn btn-sm btn-light btn-icon" title="Ver perfil e historial"><i class="bi bi-eye"></i></a>
                    <button class="btn btn-sm btn-light btn-icon" onclick="editarAlumno({{ $a->id }})" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarAlumno({{ $a->id }}, '{{ $a->nombre }}')" title="Eliminar"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron alumnos con los filtros aplicados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="px-2">
    {{ $alumnos->links() }}
</div>