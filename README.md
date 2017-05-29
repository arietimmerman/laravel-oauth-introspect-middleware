
# Laravel Middleware for OAuth 2.0 Token Introspection

Register the service provider `\ArieTimmerman\Laravel\Oauth2\ServiceProvider::class` in `config/app.php`.

Add `\ArieTimmerman\Laravel\OAuth2\VerifyAccessToken::class` as middleware.


In your `.env` file, define the following properties

~~~.properties
AUTHORIZATION_SERVER_AUTHORIZATION_URL=
AUTHORIZATION_SERVER_REDIRECT_URL=

AUTHORIZATION_SERVER_REDIRECT_URL=AUTHORIZATION_SERVER_TOKEN_URL
AUTHORIZATION_SERVER_REDIRECT_URL=AUTHORIZATION_SERVER_INTROSPECT_URL
AUTHORIZATION_SERVER_REDIRECT_URL=AUTHORIZATION_SERVER_CLIENT_ID
AUTHORIZATION_SERVER_REDIRECT_URL=AUTHORIZATION_SERVER_CLIENT_SECRET
~~~
