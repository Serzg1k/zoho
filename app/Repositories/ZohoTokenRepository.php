<?php

namespace App\Repositories;

use App\Models\ZohoToken;
use Carbon\Carbon;

class ZohoTokenRepository
{
    /** @var int */
    private const EXPIRY_BUFFER_MINUTES = 5;
    public function getValidAccessToken(int $bufferMinutes = self::EXPIRY_BUFFER_MINUTES): ?string
    {
        $row = ZohoToken::query()->latest('id')->first();

        if (!$row || !$row->access_token || !$row->expires_at) {
            return null;
        }

        if ($row->expires_at->gt(now()->addMinutes($bufferMinutes))) {
            return (string) $row->access_token;
        }

        return null;
    }

    public function saveToken(string $accessToken, int $expiresInSec): ZohoToken
    {
        return ZohoToken::query()->create([
            'access_token' => $accessToken,
            'expires_at' => Carbon::now()->addSeconds($expiresInSec),
        ]);
    }
}
