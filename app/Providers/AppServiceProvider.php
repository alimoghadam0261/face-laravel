<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Behind the reverse proxy / SSL termination all asset, Livewire and
        // route URLs must be generated as https, otherwise the browser blocks
        // them as Mixed Content (e.g. /livewire-xxxx/update -> http, blocked,
        // and modals that depend on it never open).
        if ($this->app->environment('production') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
