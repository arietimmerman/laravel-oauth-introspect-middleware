<?php
namespace DesignMyNight\Laravel\OAuth2;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Exceptions\MissingScopeException;

class VerifyAccessToken
{
    protected $accessTokenCacheKey = 'access_token';

    private $client = null;

    protected function checkScopes($scopesForToken, $requiredScopes)
    {
        if (!is_array($requiredScopes)) {
            $requiredScopes = [$requiredScopes];
        }

        $misingScopes = array_diff($scopesForToken, $scopesForToken);

        if (count($misingScopes) > 0) {
            throw new MissingScopeException($misingScopes);
        }
    }

    protected function getAccessToken(): string
    {
        $accessToken = Cache::get($this->accessTokenCacheKey);

        return $accessToken ?: $this->getNewAccessToken();
    }

    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->setClient(new Client());
        }

        return $this->client;
    }

    protected function getIntrospect($accessToken)
    {
        $response = $this->getClient()->post(config('authorizationserver.introspect_url'), [
            'form_params' => [
                'token_type_hint' => 'access_token',
                'token' => $accessToken,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            throw new AuthenticationException('No Bearer token present');
        }

        try {
            $result = $this->getIntrospect($bearerToken);

            if (!$result['active']) {
                throw new AuthenticationException('Invalid token!');
            }

            if ($scopes !== null) {
                $this->checkScopes(explode(' ', $result['scope']), $scopes);
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $result = json_decode((string) $e->getResponse()->getBody(), true);

                if (isset($result['error'])) {
                    throw new AuthenticationException($result['error']['title'] ?? '');
                }
            }

            throw new AuthenticationException($e->getMessage());
        }

        return $next($request);
    }

    protected function getNewAccessToken(): string
    {
        $response = $this->getClient()->post(config('authorizationserver.token_url'), [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => config('authorizationserver.client_id'),
                'client_secret' => config('authorizationserver.client_secret'),
                'scope' => config('authorizationserver.scope'),
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);

        if (isset($result['access_token'])) {
            $accessToken = $result['access_token'];

            Cache::add($this->accessTokenCacheKey, $accessToken, intVal($result['expires_in']) / 60);

            return $accessToken;
        }

        throw new AuthenticationException('Did not receive an access token');
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
