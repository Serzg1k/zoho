<?php

namespace App\Contracts;

interface ZohoCrmServiceContract
{
    public function createAccount(array $payload): string;

    public function createDeal(array $payload, string $accountId): string;
}
