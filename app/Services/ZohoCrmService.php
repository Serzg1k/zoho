<?php

namespace App\Services;

use App\Contracts\ZohoCrmServiceContract;
use App\Zoho\ZohoEndpoint;
use App\Support\ZohoConfig;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;

readonly class ZohoCrmService implements ZohoCrmServiceContract
{
    public function __construct(
        private ZohoApiClient $api,
        private ZohoOAuthService $oauth,
        private ZohoConfig $cfg,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function createAccount(array $payload): string
    {
        $resp = $this->crmPostWithRetry(ZohoEndpoint::CrmAccounts, [
            'data' => [[
                'Account_Name' => $payload['account_name'],
                'Website' => $payload['account_website'] ?? null,
                'Phone' => $payload['account_phone'] ?? null,
            ]],
        ]);

        return $this->extractZohoIdOrThrow($resp, 'Zoho create Account');
    }

    /**
     * @throws ConnectionException
     */
    public function createDeal(array $payload, string $accountId): string
    {
        $dealData = [
            'Deal_Name' => $payload['deal_name'],
            'Stage' => $payload['deal_stage'],
            'Account_Name' => ['id' => $accountId],
        ];

        if (!empty($payload['deal_pipeline'])) {
            $dealData['Pipeline'] = $payload['deal_pipeline'];
        }

        $resp = $this->crmPostWithRetry(ZohoEndpoint::CrmDeals, [
            'data' => [$dealData],
        ]);

        return $this->extractZohoIdOrThrow($resp, 'Zoho create Deal');
    }

    /**
     * @throws ConnectionException
     */
    private function crmPostWithRetry(ZohoEndpoint $endpoint, array $body): Response
    {
        $token = $this->oauth->getAccessToken();

        $url = $endpoint->url($this->cfg->apiBase);

        $resp = $this->api->postJson($url, $token, $body);

        if ($resp->status() === 401) {
            $token = $this->oauth->refreshAccessToken();
            $resp = $this->api->postJson($url, $token, $body);
        }

        return $resp;
    }

    private function extractZohoIdOrThrow(Response $resp, string $context): string
    {
        $json = $resp->json() ?? [];

        $status = (string) data_get($json, 'data.0.status', '');
        $code = (string) data_get($json, 'data.0.code', '');
        $message = (string) data_get($json, 'data.0.message', '');

        if ($status !== 'success' && $code !== 'SUCCESS') {
            throw new \RuntimeException(sprintf(
                '%s failed (HTTP %s). code=%s status=%s message=%s body=%s',
                $context,
                $resp->status(),
                $code ?: 'n/a',
                $status ?: 'n/a',
                $message ?: 'n/a',
                $resp->body()
            ));
        }

        $id = (string) data_get($json, 'data.0.details.id', '');
        if ($id === '') {
            throw new \RuntimeException($context . ' failed: no id returned. body=' . $resp->body());
        }

        return $id;
    }
}
