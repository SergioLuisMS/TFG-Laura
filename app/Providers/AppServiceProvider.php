<?php

namespace App\Providers;

use App\Models\Amigo;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registro los servicios de la aplicacion.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializo los servicios de la aplicacion.
     *
     * Registro la UserPolicy para que $this->authorize() en los controladores
     * la encuentre automaticamente (Seguridad #26).
     *
     * Comparto la variable solicitudesPendientes con todas las vistas para
     * que el badge del navbar siempre este actualizado.
     */
    public function boot(): void
    {
        // Registro la policy del modelo User
        Gate::policy(User::class, UserPolicy::class);

        // Comparto el conteo de solicitudes con todas las vistas de usuarios autenticados
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $conteo = Amigo::where('amigo_id', Auth::id())
                    ->where('estado', 'pendiente')
                    ->count();
                $view->with('solicitudesPendientes', $conteo);
            } else {
                $view->with('solicitudesPendientes', 0);
            }
        });
    }
}
