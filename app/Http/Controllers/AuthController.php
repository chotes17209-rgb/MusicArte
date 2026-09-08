<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
            'password.required' => 'La contrasena es obligatoria.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (! Auth::user()->activo) {
                Auth::logout();

                return back()->withErrors(['email' => 'Tu cuenta esta inactiva. Contacta al administrador.']);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Seccion 15: pantalla para reconfirmar la contrasena antes de entrar
     * al modulo de Pagos (informacion sensible), usando las credenciales
     * del usuario ya logueado.
     */
    public function mostrarConfirmarPagos()
    {
        return view('auth.confirmar-pagos');
    }

    public function confirmarPagos(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ], [
            'password.required' => 'Ingresa tu contrasena para continuar.',
        ]);

        if (! Auth::guard()->validate([
            'email' => $request->user()->email,
            'password' => $request->input('password'),
        ])) {
            return back()->withErrors(['password' => 'La contrasena no es correcta.']);
        }

        $request->session()->put('pagos_confirmado_en', now());
        $destino = $request->session()->pull('pagos_url_intentada');

        return redirect($destino ?: route('pagos.index'));
    }
}
