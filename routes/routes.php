<?php

use DesignMyNight\Laravel\OAuth2\Http\Controllers\RedirectImplicitGrant;

/**
 * Redirecting endpoint for initiating the OAuth2 Implicit Grant flow.
 * The retrieved access token can be used to call the APIs as protected with the provided middleware.
 *
 * Note: this module does not provide any logic for extracting the access tokens from the url.
 *
 */
Route::get('/redirect', RedirectImplicitGrant::class);
