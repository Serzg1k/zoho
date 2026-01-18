<?php

namespace App\Support;

final readonly class ZohoConfig
{
    public function __construct(
        public string $accountsUrl,
        public string $apiBase,
        public string $clientId,
        public string $clientSecret,
        public string $refreshToken,
    ) {}

    public function assertValid(): void
    {
        foreach ([
                     'accountsUrl' => $this->accountsUrl,
                     'apiBase' => $this->apiBase,
                     'clientId' => $this->clientId,
                     'clientSecret' => $this->clientSecret,
                     'refreshToken' => $this->refreshToken,
                 ] as $key => $value) {
            if (trim($value) === '') {
                throw new \RuntimeException("Zoho config is missing: {$key}");
            }
        }
    }
}
