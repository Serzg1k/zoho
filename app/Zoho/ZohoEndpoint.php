<?php

namespace App\Zoho;

enum ZohoEndpoint: string
{
    // OAuth
    case OAuthToken = '/oauth/v2/token';

    // CRM v8
    case CrmAccounts = '/crm/v8/Accounts';
    case CrmDeals = '/crm/v8/Deals';

    public function url(string $base): string
    {
        return rtrim($base, '/') . $this->value;
    }
}
