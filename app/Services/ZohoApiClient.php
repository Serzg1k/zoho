<?php

namespace App\Services;

use App\Zoho\ZohoEndpoint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

readonly class ZohoApiClient
{


    /**
     * @throws ConnectionException
     */
    public function oauthRefreshToken(ZohoEndpoint $endpoint, string $accountsBase, array $params): Response
    {
        $url = $endpoint->url($accountsBase) . '?' . http_build_query($params);

        return Http::timeout(20)->post($url);
    }

    /**
     * @throws ConnectionException
     */

    public function postJson(string $url, string $accessToken, array $body): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(20)->post($url, $body);
    }
}
