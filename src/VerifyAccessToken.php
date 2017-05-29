<?php
/**
 * Middleware for verifying the Bearer OAuth2 access token as provided in the HTTP Authorization-header. 
 */

namespace ArieTimmerman\Laravel\OAuth2;

use Closure;
use Illuminate\Auth\AuthenticationException;
use GuzzleHttp\Exception\RequestException;

class VerifyAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
    	
    	$authorization = $request->header('Authorization');
    	$receivedAccessToken = preg_replace('/^Bearer /', '', $authorization);
    	
    	$guzzle = new \GuzzleHttp\Client;
    	
    	//This is the access token for verifying the user's access token
    	$accessToken = cache('accessToken');
    	
    	if(!$accessToken){
    		
    		$response = $guzzle->post(config('authorizationserver.authorization_server_token_url'), [
    				'form_params' => [
    						'grant_type' => 'client_credentials',
    						'client_id' => config('authorizationserver.authorization_server_client_id'),
    						'client_secret' => config('authorizationserver.authorization_server_client_secret'),
    						'scope' => '',
    				],
    		]);
    		
    		$result = json_decode((string) $response->getBody(), true);
    		
    		$accessToken = $result['access_token'];
    		
    		\Cache::add('accessToken', $accessToken, intVal($result['expires_in'])/60);
    		
    	}
    	
    	//Now verify the user provided access token
    	try{
    	$response = $guzzle->post(config('authorizationserver.authorization_server_introspect_url'), [
    			'form_params' => [
    					'token_type_hint' => 'access_token',
    					'token' => $receivedAccessToken,
    			],
    			'headers' => [
    					'Authorization'     => 'Bearer ' . $accessToken,
    			]
    	]);
    	
    	$result = json_decode((string) $response->getBody(), true);
    	
    	if (!$result['active']){
    		throw new AuthenticationException("Invalid token!");
    	}
    	}catch(RequestException $e){
    		if($e->hasResponse()){
    			$result = json_decode((string)$e->getResponse()->getBody(), true);
    			
    			if(isset($result['error'])){
    				throw new AuthenticationException($result['error']['title'] ?? "Invalid token!");
    			}else{
    				throw new AuthenticationException("Invalid token!");
    			}
    		}else{
    			throw new AuthenticationException($e);
    		}
    	}
    	
    	$request->attributes->add(["introspect"=>$result]);
    	
        return $next($request);
        
    }
}
