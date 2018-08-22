<?php

namespace DesignMyNight\Laravel\OAuth2\Facades;

use DesignMyNight\Laravel\OAuth2\Introspect as IntrospectService;
use Illuminate\Support\Facades\Facade;

/**
 * Class Introspect
 *
 * @package DesignMyNight\Laravel\OAuth2\Facades
 *
 * @method static void mustHaveScopes(array $requiredScopes = [])
 * @method static \DesignMyNight\Laravel\OAuth2\Intropect setUserDataKey(string $key)
 * @method static \DesignMyNight\Laravel\OAuth2\Intropect setUserModelClass(string $class)
 * @method static bool tokenIsActive()
 * @method static bool tokenIsNotActive()
 * @method static mixed|null getUser()
 * @method static \Illuminate\Contracts\Auth\Authenticatable getUserModel()
 * @method static string getUserModelClass()
 * @method static \DesignMyNight\Laravel\OAuth2\Intropect verifyToken()
 */
class Introspect extends Facade
{
    protected static function getFacadeAccessor()
    {
        return IntrospectService::class;
    }
}
