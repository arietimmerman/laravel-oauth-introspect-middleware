<?php
namespace DesignMyNight\Laravel\OAuth2;

use Closure;

class VerifyAccessToken
{
    protected $introspect;

    public function __construct(Introspect $introspect)
    {
        $this->introspect = $introspect;
    }

    public function handle($request, Closure $next, ...$scopes)
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];

        $this->introspect
            ->verifyToken()
            ->mustHaveScopes($scopes);

        return $next($request);
    }
}
