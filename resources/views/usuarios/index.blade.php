@extends('layouts.app')
@section('titulo', 'Usuarios')

@section('contenido')
<x-page-head titulo="Usuarios" subtitulo="Personas que pueden entrar al sistema. El administrador ve todo; recepción no puede cambiar precios ni pagos.">
    <button class="btn btn-morado" onclick="nuevoUsuario()"><i class="bi bi-plus-lg me-1"></i> Nuevo usuario</button>
</x-page-head>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @foreach($usuarios as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar">{{ collect(explode(' ', $u->name))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') }}</span>
                            <div class="min-w-0">
                                <div class="fw-semibold">{{ $u->name }} @if($u->is(auth()->user()))<span class="badge bg-primary ms-1">Tú</span>@endif</div>
                                <div class="small text-muted">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $u->rolLabel() }}</td>
                    <td>@if($u->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Sin acceso</span>@endif</td>
                    <td class="text-end">
                        <a href="{{ route('usuarios.show', $u) }}" class="btn btn-sm btn-light btn-icon" title="Ver"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarUsuario({{ $u->id }})" title="Editar"><i class="bi bi-pencil"></i></button>
                        @unless($u->is(auth()->user()))
                            <button class="btn btn-sm btn-light btn-icon" onclick="eliminarUsuario({{ $u->id }}, @js($u->name))" title="Eliminar"><i class="bi bi-trash"></i></button>
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formUsuario" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalUsuario">Nuevo usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="usuario_id">
                <div class="mb-3">
                    <label class="form-label" for="usuario_name">Nombre completo</label>
                    <input type="text" class="form-control" id="usuario_name" required maxlength="120">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="usuario_email">Correo (con este inicia sesión)</label>
                    <input type="email" class="form-control" id="usuario_email" required maxlength="150">
                </div>
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="form-label" for="usuario_role">Rol</label>
                        <select class="form-select" id="usuario_role">
                            <option value="recepcion">Recepción</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label" for="usuario_password">Contraseña</label>
                        <input type="password" class="form-control" id="usuario_password" minlength="8" autocomplete="new-password">
                        <div class="form-text" id="ayudaPassword">Mínimo 8 caracteres.</div>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="usuario_activo" checked>
                    <label class="form-check-label" for="usuario_activo">Puede iniciar sesión</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const modalUsuario = new bootstrap.Modal('#modalUsuario');
    const campo = (id) => document.getElementById(`usuario_${id}`);

    function nuevoUsuario() {
        document.getElementById('formUsuario').reset();
        campo('id').value = '';
        campo('password').required = true;
        document.getElementById('ayudaPassword').textContent = 'Mínimo 8 caracteres.';
        document.getElementById('tituloModalUsuario').textContent = 'Nuevo usuario';
        modalUsuario.show();
    }

    async function editarUsuario(id) {
        const res = await maFetch(`/usuarios/${id}/edit`);
        if (!res) return;
        const d = res.data;
        campo('id').value = d.id;
        campo('name').value = d.name;
        campo('email').value = d.email;
        campo('role').value = d.role;
        campo('activo').checked = !!d.activo;
        campo('password').value = '';
        campo('password').required = false;
        document.getElementById('ayudaPassword').textContent = 'Déjalo vacío para mantener la contraseña actual.';
        document.getElementById('tituloModalUsuario').textContent = 'Editar usuario';
        modalUsuario.show();
    }

    document.getElementById('formUsuario').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = campo('id').value;
        const payload = {
            name: campo('name').value,
            email: campo('email').value,
            role: campo('role').value,
            activo: campo('activo').checked ? 1 : 0,
            password: campo('password').value || null,
        };
        const res = await maFetch(id ? `/usuarios/${id}` : '/usuarios', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalUsuario.hide();
            setTimeout(() => location.reload(), 600);
        }
    });

    async function eliminarUsuario(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/usuarios/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 600);
        }
    }
</script>
@endpush
