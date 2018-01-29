<?php
namespace DesignMyNight\Laravel\OAuth2;

use Closure;
use DesignMyNight\Laravel\OAuth2\Exceptions\InvalidAccessTokenException;
use DesignMyNight\Laravel\OAuth2\Exceptions\InvalidEndpointException;
use DesignMyNight\Laravel\OAuth2\Exceptions\InvalidInputException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class VerifyAccessToken
{
    private $client = null;

    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->setClient(new Client());
        }

        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    protected function getIntrospect($accessToken)
    {
        $guzzle = $this->getClient();

        $response = $guzzle->post(config('authorizationserver.introspect_url'), [
            'form_params' => [
                'token_type_hint' => 'access_token',

                // This is the access token for verifying the user's access token
                'token' => $accessToken,
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function getAccessToken(): string
    {
        $accessToken = Cache::get('access_token');

        if (!$accessToken) {
            $guzzle = $this->getClient();

            $response = $guzzle->post(config('authorizationserver.token_url'), [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('authorizationserver.client_id'),
                    'client_secret' => config('authorizationserver.client_secret'),
                    'scope' => '',
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            if ($result && isset($result['access_token'])) {
                $accessToken = $result['access_token'];

                Cache::add('access_token', $accessToken, intVal($result['expires_in']) / 60);
            } else {
                throw new InvalidEndpointException('Did not receive an access token');
            }
        }

        return $accessToken;
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
        $authorization = $request->header('Authorization');

        if (!$authorization) {
            throw new InvalidInputException('No Authorization header present');
        }

        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            throw new InvalidInputException('No Bearer token in the Authorization header present');
        }

        // Now verify the user provided access token
        try {
            $result = $this->getIntrospect($bearerToken);
            if (!$result['active']) {
                throw new InvalidAccessTokenException('Invalid token!');
            } else if ($scopes != null) {
                if (!\is_array($scopes)) {
                    $scopes = [
                        $scopes,
                    ];
                }

                $scopesForToken = \explode(' ', $result['scope']);

                if (count($misingScopes = array_diff($scopes, $scopesForToken)) > 0) {
                    throw new InvalidAccessTokenException('Missing the following required scopes: ' . implode(' ,', $misingScopes));
                } else {
                }
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $result = json_decode((string) $e->getResponse()->getBody(), true);

                var_dump($result);exit;

                if (isset($result['error'])) {
                    throw new InvalidAccessTokenException($result['error']['title'] ?? 'Invalid token!');
                } else {
                    throw new InvalidAccessTokenException('Invalid token!');
                }
            } else {
                throw new InvalidAccessTokenException($e);
            }
        }

        return $next($request);
    }
}
