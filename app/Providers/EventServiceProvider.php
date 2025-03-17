<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Liste des événements et leurs écouteurs.
     */
    protected $listen = [
        // 'App\Events\SomeEvent' => [
        //     'App\Listeners\SomeListener',
        // ],
    ];

    /**
     * Démarrer les services liés aux événements.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
