<?php

namespace App\Providers;

use App\Contracts\ZohoCrmServiceContract;
use App\Services\ZohoCrmService;
use App\Support\ZohoConfig;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ZohoCrmServiceContract::class,
            ZohoCrmService::class
        );

        $this->app->singleton(ZohoConfig::class, function () {
            $cfg = new ZohoConfig(
                accountsUrl: rtrim((string) config('zoho.accounts_url'), '/'),
                apiBase: rtrim((string) config('zoho.api_base'), '/'),
                clientId: trim((string) config('zoho.client_id')),
                clientSecret: trim((string) config('zoho.client_secret')),
                refreshToken: trim((string) config('zoho.refresh_token')),
            );

            $cfg->assertValid();

            return $cfg;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
