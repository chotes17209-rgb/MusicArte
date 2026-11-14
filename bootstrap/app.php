<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Railway termina HTTPS en su proxy y envía
        // X-Forwarded-Proto: https hacia Laravel.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\CerrarPeriodosVencidos::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'pagos.auth' => \App\Http\Middleware\EnsurePagosAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // "Page Expired" (419): la pagina abierta tenia un token viejo (por
        // ejemplo, el formulario de ingreso quedo abierto en el celular o se
        // volvio con el boton Atras). En vez de la pantalla de error, se
        // vuelve a cargar la pagina con un token nuevo para reintentar.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
                return null;
            }
            if ($request->routeIs('logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login');
            }
            if ($request->routeIs('login.attempt')) {
                return \Illuminate\Support\Facades\Auth::check()
                    ? redirect()->route('dashboard')
                    : redirect()->route('login')->withInput($request->only('email'))
                        ->with('aviso', 'La página se actualizó. Vuelve a escribir tu contraseña e ingresa.');
            }

            return redirect()->back()->withInput($request->except('password', '_token'))
                ->with('aviso', 'La página se actualizó. Vuelve a intentarlo.');
        });
    })
    ->create();