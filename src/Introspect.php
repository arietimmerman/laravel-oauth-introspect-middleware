<?php

namespace DesignMyNight\Laravel\OAuth2;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\MissingScopeException;

class Introspect
{
    protected $accessTokenCacheKey = 'access_token';

    protected $client = null;
    protected $result;

    public function __construct(IntrospectClient $client, Request $request)
    {
        $this->client = $client;
        $this->request = $request;
    }

    protected function getIntrospectionResult()
    {
        if ($this->result === null) {
            try {
                $this->result = $this->client->introspect($this->request->bearerToken());
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

    public function mustHaveScopes(array $requiredScopes = [])
    {
        $result = $this->getIntrospectionResult();
        $givenScopes = explode(' ', $result['scope']);
        $misingScopes = array_diff($requiredScopes, $givenScopes);

        if (count($misingScopes) > 0) {
            throw new MissingScopeException($misingScopes);
        }
    }

    public function tokenIsActive(): bool
    {
        $result = $this->getIntrospectionResult();

        return $result['active'] === true;
    }

    public function tokenIsNotActive(): bool
    {
        return !$this->tokenIsActive();
    }

    public function verifyToken()
    {
        if ($this->tokenIsNotActive()) {
            throw new AuthenticationException('Invalid token');
        }

        return $this;
    }
}
