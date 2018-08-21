<?php

namespace DesignMyNight\Laravel\OAuth2\Facades;

use DesignMyNight\Laravel\OAuth2\Introspect as IntrospectService;
use Illuminate\Support\Facades\Facade;

class Introspect extends Facade
{
    protected static function getFacadeAccessor()
    {
        return IntrospectService::class;
    }
}
