<?php

namespace App\Services;

use App\Repositories\ZohoTokenRepository;
use App\Support\ZohoConfig;
use App\Zoho\ZohoEndpoint;
use Illuminate\Http\Client\ConnectionException;

readonly class ZohoOAuthService
{

    public function __construct(
        private ZohoApiClient       $api,
        private ZohoTokenRepository $tokens,
        private ZohoConfig $cfg,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function getAccessToken(): string
    {
        $token = $this->tokens->getValidAccessToken();

        if ($token) {
            return $token;
        }

        return $this->refreshAccessToken();
    }

    /**
     * @throws ConnectionException
     */
    public function refreshAccessToken(): string
    {
        $resp = $this->api->oauthRefreshToken(ZohoEndpoint::OAuthToken, $this->cfg->accountsUrl, [
            'refresh_token' => $this->cfg->refreshToken,
            'client_id' => $this->cfg->clientId,
            'client_secret' => $this->cfg->clientSecret,
            'grant_type' => 'refresh_token',
        ]);

        if (!$resp->ok()) {
            throw new \RuntimeException('Zoho refresh failed: ' . $resp->status() . ' ' . $resp->body());
        }

        $data = $resp->json() ?? [];
        $accessToken = $data['access_token'] ?? null;
        $expiresIn = (int)($data['expires_in_sec'] ?? 3600);

        if (!$accessToken) {
            throw new \RuntimeException('Zoho refresh did not return access_token: ' . $resp->body());
        }

        $this->tokens->saveToken($accessToken, $expiresIn);

        return $accessToken;
    }
}
