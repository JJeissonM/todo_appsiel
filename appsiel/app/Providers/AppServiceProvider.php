<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        config([
            'session.lifetime' => env('SESSION_LIFETIME', config('session.lifetime')),
            'session.cookie' => env('SESSION_COOKIE', config('session.cookie')),
            'session.same_site' => env('SESSION_SAME_SITE', config('session.same_site')),
            'session.domain' => env('SESSION_DOMAIN', config('session.domain')),
            'session.secure' => env('SESSION_SECURE_COOKIE', config('session.secure')),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\App\Core\Services\TurnoContext::class, function () {
            return new \App\Core\Services\TurnoContext();
        });
        $this->app->singleton(\App\Core\Services\TurnoModeResolver::class, function () {
            return new \App\Core\Services\TurnoModeResolver();
        });
        $this->app->singleton(\App\Core\Services\TurnoPresentationService::class, function () {
            return new \App\Core\Services\TurnoPresentationService();
        });

        if ($this->app->environment() !== 'production')
        {
            $this->app->register(\Way\Generators\GeneratorsServiceProvider::class);
            $this->app->register(\Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
        }
    }
}
