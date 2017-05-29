
Especially for a microservices architecture, authentication and authorization functions should be delegated. Protecting resources is best done by implementing the web services as a pure OAuth2 resource server, relying on token verification on a remote authorization server.

# Laravel Middleware for OAuth 2.0 Token Introspection

Laravel Passport provides a full OAuth2 server implementation, yet misses optional OAuth2 functionalties as defined in OAuth 2.0 Token Introspection (RFC7662).

The Introspection endpoint is provided by [ipunkt/laravel-oauth-introspection](https://github.com/ipunkt/laravel-oauth-introspection). This package provides the middleware required for verifying an access token against a remote Introspection endpoint.

__Note__: To prevent token scanning attacks, the endpoint MUST also require some form of authorization to access this endpoint. The provided middleware assumes the introspection endpoint requires an OAuth2 Bearer token retrieved using a client credentials grant. Therefore, you MUST provide a valid _client id_ and _client secret_.

# Installation

Register the service provider `\ArieTimmerman\Laravel\Oauth2\ServiceProvider::class` in `config/app.php`.

In `App/Http/Kernel.php`, add `\ArieTimmerman\Laravel\OAuth2\VerifyAccessToken::class` as middleware. This should look like the following.

~~~.php
protected $routeMiddleware = [
	
	// ...
	
    'verifyaccesstoken' => \ArieTimmerman\Laravel\OAuth2\VerifyAccessToken::class,
    
    // ...
    
];
~~~    

In your `.env` file, define the following properties

~~~.properties
# Url of the authorization server
AUTHORIZATION_SERVER_URL="https://authorization.server.dom"
# Client Identifier as defined in https://tools.ietf.org/html/rfc6749#section-2.2
AUTHORIZATION_SERVER_CLIENT_ID="123"
# The client secret
AUTHORIZATION_SERVER_CLIENT_SECRET="abcdefg"
# Endpoint for requesting the access token
AUTHORIZATION_SERVER_TOKEN_URL="${AUTHORIZATION_SERVER_URL}/oauth/token"
# The OAuth2 Introspection endpoint https://tools.ietf.org/html/rfc7662
AUTHORIZATION_SERVER_INTROSPECT_URL="${AUTHORIZATION_SERVER_URL}/oauth/introspect"

# Optional configuration for requesting an OAuth2 access tokens using the implicit grant flow 
AUTHORIZATION_SERVER_AUTHORIZATION_URL="${AUTHORIZATION_SERVER_URL}/oauth/authorize"
AUTHORIZATION_SERVER_REDIRECT_URL=https://my.machine.dom
~~~

