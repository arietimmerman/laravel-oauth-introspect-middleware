<?php

namespace DesignMyNight\Laravel\OAuth2;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Exceptions\MissingScopeException;

class Introspect
{
    protected $accessTokenCacheKey = 'access_token';

    private $client = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function verifyToken()
    {
        $result = $this->getIntrospectionResult();

        if (!$result['active']) {
            throw new AuthenticationException('Invalid token!');
        }
    }

    public function mustHaveScopes(array $requiredScopes = [])
    {
        $result = $this->getIntrospectionResult();
        $givenScopes = explode(' ', $result['scope']);
        $misingScopes = array_diff($requiredScopes, $givenScopes);

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

    protected function getIntrospectionResult()
    {
        if ($this->result === null) {
            try {
                $this->result = $this->makeIntrospectionRequest();
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $result = json_decode((string) $e->getResponse()->getBody(), true);

                    if (isset($result['error'])) {
                        throw new AuthenticationException($result['error']['title'] ?? '');
                    }
                }

                throw new AuthenticationException($e->getMessage());
            }
        }

        return $this->result;
    }

    protected function getNewAccessToken(): string
    {
        $response = $this->getClient()->post(config('authorizationserver.token_url'), [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => config('authorizationserver.client_id'),
                'client_secret' => config('authorizationserver.client_secret'),
                'scope' => '',
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

    protected function makeIntrospectionRequest()
    {
        $response = $this->getClient()->post(config('authorizationserver.introspect_url'), [
            'form_params' => [
                'token_type_hint' => 'access_token',
                'token' => $this->request->bearerToken(),
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
