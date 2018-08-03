<?php

namespace DesignMyNight\Laravel\OAuth2;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class IntrospectMiddlewareServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $source = realpath(__DIR__ . '/../config/authorizationserver.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('authorizationserver.php')]);
        } else if ($this->app instanceof LumenApplication) {
            $this->app->configure('authorizationserver');
        }

        $routes = realpath(__DIR__ . '/../routes/routes.php');

        $this->loadRoutesFrom($routes);
        $this->mergeConfigFrom($source, 'authorizationserver');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $request = $this->app->make(Request::class);
        $introspect = new Introspect($request);

        $this->app->singleton(Introspect::class, function() use($introspect) {
            return $introspect;
        });

        Auth::extend('introspect', function () use($introspect) {
            return new IntrospectGuard($introspect);
        });
    }
}
