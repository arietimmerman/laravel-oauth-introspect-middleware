<?php

/**
 * Redirecting endpoint for initiating the OAuth2 Implicit Grant flow.
 * The retrieved access token can be used to call the APIs as protected with the provided middleware.
 *
 * Note: this module does not provide any logic for extracting the access tokens from the url.
 *
 */
Route::get('/redirect', function (Request $request) {
    $query = http_build_query([
        'client_id' => config('authorizationserver.client_id'),
        'redirect_uri' => config('authorizationserver.redirect_url'),
        'response_type' => 'token',
        'scope' => 'place-orders',
    ]);

    return redirect(config('authorizationserver.authorization_url') . '?' . $query);
});
