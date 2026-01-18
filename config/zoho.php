<?php


return [
    'accounts_url' => rtrim(env('ZOHO_ACCOUNTS_URL', ''), '/'),
    'api_base' => rtrim(env('ZOHO_API_BASE', ''), '/'),
    'client_id' => env('ZOHO_CLIENT_ID', ''),
    'client_secret' => env('ZOHO_CLIENT_SECRET', ''),
    'refresh_token' => env('ZOHO_REFRESH_TOKEN', ''),
];
