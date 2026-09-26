<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Usuarios del sistema (quienes inician sesion): administrador y
 * recepcion. Solo el administrador entra a este modulo (ver rutas).
 */
class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderByDesc('activo')->orderBy('name')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function show(User $usuario)
    {
        return view('usuarios.show', compact('usuario'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDatos($request);
        $usuario = User::create($data);

        return response()->json(['ok' => true, 'message' => "Usuario {$usuario->name} creado.", 'data' => $usuario]);
    }

    public function edit(User $usuario)
    {
        return response()->json(['ok' => true, 'data' => $usuario->only(['id', 'name', 'email', 'role', 'activo'])]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $this->validarDatos($request, $usuario);

        // La contrasena solo cambia si se escribe una nueva.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($error = $this->protegerUltimoAdmin($usuario, $data)) {
            return $error;
        }

        $usuario->update($data);

        return response()->json(['ok' => true, 'message' => 'Usuario actualizado.', 'data' => $usuario]);
    }

    public function destroy(Request $request, User $usuario)
    {
        if ($usuario->is($request->user())) {
            return response()->json(['ok' => false, 'message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        if ($error = $this->protegerUltimoAdmin($usuario, ['role' => 'recepcion'])) {
            return $error;
        }

        $usuario->delete();

        return response()->json(['ok' => true, 'message' => 'Usuario eliminado.']);
    }

    /** Evita quedarse sin ningun administrador activo (nadie podria administrar el sistema). */
    private function protegerUltimoAdmin(User $usuario, array $cambios)
    {
        $dejaDeSerAdmin = ($cambios['role'] ?? $usuario->role) !== 'admin' || (array_key_exists('activo', $cambios) && ! $cambios['activo']);
        $otrosAdmins = User::where('role', 'admin')->where('activo', true)->whereKeyNot($usuario->id)->exists();

        if ($usuario->esAdmin() && $usuario->activo && $dejaDeSerAdmin && ! $otrosAdmins) {
            return response()->json(['ok' => false, 'message' => 'Debe quedar al menos un administrador activo.'], 422);
        }

        return null;
    }

    private function validarDatos(Request $request, ?User $usuario = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($usuario?->id)],
            'role' => 'required|in:admin,recepcion',
            'activo' => 'nullable|boolean',
            'password' => [$usuario ? 'nullable' : 'required', 'string', Password::min(8)],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'password.required' => 'Escribe una contraseña para el nuevo usuario.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        return $data;
    }
}
