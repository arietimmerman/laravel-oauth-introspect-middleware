<?php

namespace DesignMyNight\Laravel\OAuth2;

use GuzzleHttp\Client;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\Repository as Cache;

class IntrospectClient
{
    protected $accessTokenCacheKey = 'access_token';

    protected $cache;
    protected $client;
    protected $config;

    public function __construct(array $config, Cache $cache)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    protected function getAccessToken(): string
    {
        $accessToken = $this->cache->get($this->accessTokenCacheKey);

        return $accessToken ?: $this->getNewAccessToken();
    }

    protected function getClient(): Client
    {
        if ($this->client === null) {
            $this->setClient(new Client());
        }

        return $this->client;
    }

    protected function getNewAccessToken(): string
    {
        $response = $this->getClient()->post($this->config['token_url'], [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'scope' => $this->config['scope'],
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);

        if (isset($result['access_token'])) {
            $accessToken = $result['access_token'];

            $this->cache->add($this->accessTokenCacheKey, $accessToken, intVal($result['expires_in']) / 60);

            return $accessToken;
        }

        throw new AuthenticationException('Did not receive an access token');
    }

    public function introspect(string $token)
    {
        $response = $this->getClient()->post($this->config['introspect_url'], [
            'form_params' => [
                'token_type_hint' => 'access_token',
                'token' => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
