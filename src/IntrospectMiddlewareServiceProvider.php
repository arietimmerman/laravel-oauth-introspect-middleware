<?php
namespace DesignMyNight\Laravel\OAuth2;

use Illuminate\Foundation\Application as LaravelApplication;
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
    }
}
