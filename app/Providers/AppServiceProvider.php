<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <--- IMPORTANTE: Añade esta línea
use App\Models\User; // <--- IMPORTANTE: Añade esta línea

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
        // DEFINICIÓN DE SELLOS (GATES) PARA LOS ROLES
        
        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('profesor', function (User $user) {
            return $user->role === 'profesor';
        });

        Gate::define('alumno', function (User $user) {
            return $user->role === 'alumno';
        });
    }
}