<?php

namespace ArieTimmerman\Laravel\OAuth2;

use ArieTimmerman\Laravel\OAuth2\Exceptions\InvalidAccessTokenException;

class VerifyAccessTokenHasAnyScope extends VerifyAccessToken
{
    protected function checkScopes($scopes, $scopesForToken)
    {
        $match = false;
        foreach ($scopes as $scope) {
            if (in_array($scope, $scopesForToken)) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            throw new InvalidAccessTokenException(
                "Missing one the following scopes: " . implode(" ,", $scopes)
            );
        }
    }
}
