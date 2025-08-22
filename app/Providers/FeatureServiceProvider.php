<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\ServiceProvider;

class FeatureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(StatefulGuard::class, fn($app) => $app['auth']->guard('web'));
        $this->app->bind(PasswordBroker::class, fn() => Password::broker());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
