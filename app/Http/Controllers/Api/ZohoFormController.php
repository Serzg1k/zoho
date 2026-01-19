<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ZohoCrmServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\ZohoSubmitRequest;

class ZohoFormController extends Controller
{
    public function submit(ZohoSubmitRequest $request, ZohoCrmServiceContract $zoho)
    {
        $data = $request->validated();
        try {
            // Create Account first
            $accountId = $zoho->createAccount($data);

            // Create Deal linked to Account
            $dealId = $zoho->createDeal($data, $accountId);

            return response()->json([
                'ok' => true,
                'account_id' => $accountId,
                'deal_id' => $dealId,
                'message' => 'Account and Deal created successfully',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to create records',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
