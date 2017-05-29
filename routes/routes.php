<?php

/**
 * OAuth2 Implicit Grant
 */
Route::get('/redirect', function (Request $request) {
	$query = http_build_query([
			'client_id' => config('authorizationserver.authorization_server_client_id'),
			'redirect_uri' => config('authorizationserver.authorization_server_redirect_url'),
			'response_type' => 'token',
			'scope' => '',
	]);

	return redirect(config('authorizationserver.authorization_server_authorization_url') . '?' . $query);
});
