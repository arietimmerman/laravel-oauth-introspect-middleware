<?php

namespace DesignMyNight\Laravel\OAuth2;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\MissingScopeException;

class Introspect
{
    protected $accessTokenCacheKey = 'access_token';
    protected $client = null;
    protected $result;
    protected $userDataKey = 'user';
    protected $userModelClass = User::class;

    public function __construct(IntrospectClient $client, Request $request)
    {
        $this->client = $client;
        $this->request = $request;
    }

    protected function getIntrospectionResult()
    {
        if ($this->result !== null) {
            return $this->result;
        }

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

        return $this->result;
    }

    public function mustHaveScopes(array $requiredScopes = [])
    {
        $result = $this->getIntrospectionResult();
        $givenScopes = explode(' ', $result['scope']);
        $missingScopes = array_diff($requiredScopes, $givenScopes);

        if (count($missingScopes) > 0) {
            throw new MissingScopeException($missingScopes);
        }
    }

    public function setUserDataKey(string $key): self
    {
        $this->userDataKey = $key;

        return $this;
    }

    public function setUserModelClass(string $class): self
    {
        $this->userModelClass = $class;

        return $this;
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

    public function getUser()
    {
        $result = $this->getIntrospectionResult();

        if (isset($result[$this->userDataKey]) && !empty($result[$this->userDataKey])) {
            $user = $this->getUserModel();
            $user->forceFill($result[$this->userDataKey]);

            return $user;
        }

        return null;
    }

    public function getUserModel(): Authenticatable
    {
        $class = $this->getUserModelClass();

        return new $class();
    }

    public function getUserModelClass(): string
    {
        return $this->userModelClass;
    }

    public function verifyToken()
    {
        if ($this->tokenIsNotActive()) {
            throw new AuthenticationException('Invalid token');
        }

        return $this;
    }
}
