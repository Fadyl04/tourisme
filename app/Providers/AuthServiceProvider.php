<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Liste des politiques d'autorisation.
     */
    protected $policies = [
        // 'App\Models\User' => 'App\Policies\UserPolicy',
    ];

    /**
     * Démarrer les services liés à l'authentification.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Exemple de définition d'une règle d'autorisation
        // Gate::define('update-post', function ($user, $post) {
        //     return $user->id === $post->user_id;
        // });
    }
}
