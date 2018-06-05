<?php

namespace DesignMyNight\Laravel\OAuth2\Http\Controllers;

use Illuminate\Routing\Controller;

class RedirectImplicitGrant extends Controller
{
    public function __invoke()
    {
        $query = http_build_query([
            'client_id' => config('authorizationserver.client_id'),
            'redirect_uri' => config('authorizationserver.redirect_url'),
            'response_type' => 'token',
            'scope' => 'place-orders',
        ]);

        return redirect(config('authorizationserver.authorization_url') . '?' . $query);
    }
}
