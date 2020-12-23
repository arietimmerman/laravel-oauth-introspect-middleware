<?php
/**
 * Laravel ServiceProvider for registering the routes and publishing the configuration.
 */

namespace ArieTimmerman\Laravel\OAuth2;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'authorizationserver.php' => config_path('authorizationserver.php'),
            ]
        );
        
        $this->loadRoutesFrom(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'routes.php');
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
