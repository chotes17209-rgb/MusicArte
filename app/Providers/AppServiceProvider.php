<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Aviso;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // La interfaz usa Bootstrap 5, asi que la paginacion tambien.
        Paginator::useBootstrapFive();

        // Comparte los avisos (ventanas flotantes) activos en todas las vistas
        // autenticadas sin tener que repetirlo en cada controlador.
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $avisos = Aviso::activosParaHoy()->get();
            } else {
                $avisos = collect();
            }
            $view->with('avisosFlotantes', $avisos);
        });
    }
}
