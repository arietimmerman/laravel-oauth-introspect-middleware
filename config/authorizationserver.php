<?php

return [
    'authorization_url' => env('AUTHORIZATION_SERVER_AUTHORIZATION_URL'),
    'redirect_url' => env('AUTHORIZATION_SERVER_REDIRECT_URL'),
    'token_url' => env('AUTHORIZATION_SERVER_TOKEN_URL'),
    'introspect_url' => env('AUTHORIZATION_SERVER_INTROSPECT_URL'),
    'client_id' => env('AUTHORIZATION_SERVER_CLIENT_ID'),
    'client_secret' => env('AUTHORIZATION_SERVER_CLIENT_SECRET'),
    'scope' => env('AUTHORIZATION_SERVER_SCOPE'),
];
