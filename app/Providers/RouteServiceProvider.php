<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Définissez votre espace de contrôleur par défaut.
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Chargez les routes pour votre application.
     *
     * @return void
     */
    public const HOME = '/home';
    
    public function boot()
    {
        $this->routes(function () {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->namespace($this->namespace)
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }
}
