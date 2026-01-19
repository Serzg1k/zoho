<?php

namespace Tests\Feature;

use App\Contracts\ZohoCrmServiceContract;
use Mockery;
use Tests\TestCase;

class ZohoSubmitTest extends TestCase
{
    public function test_validation_errors(): void
    {
        $resp = $this->postJson('/api/zoho/submit', [
            // intentionally empty
        ]);

        $resp->assertStatus(422);
        $resp->assertJsonValidationErrors([
            'deal_name',
            'deal_stage',
            'account_name',
        ]);
    }

    public function test_success_creates_account_and_deal(): void
    {
        $mock = Mockery::mock(ZohoCrmServiceContract::class);
        $mock->shouldReceive('createAccount')
            ->once()
            ->andReturn('ACC_ID_1');

        $mock->shouldReceive('createDeal')
            ->once()
            ->with(Mockery::type('array'), 'ACC_ID_1')
            ->andReturn('DEAL_ID_1');

        $this->app->instance(ZohoCrmServiceContract::class, $mock);

        $resp = $this->postJson('/api/zoho/submit', [
            'deal_name' => 'Test Deal',
            'deal_stage' => 'Qualification',
            'deal_pipeline' => null,

            'account_name' => 'Test Account',
            'account_website' => 'https://example.com',
            'account_phone' => '+123456',
        ]);

        $resp->assertOk();
        $resp->assertJson([
            'ok' => true,
            'account_id' => 'ACC_ID_1',
            'deal_id' => 'DEAL_ID_1',
        ]);
    }

    public function test_failure_returns_error_message(): void
    {
        $this->app->instance(ZohoCrmServiceContract::class, new class implements ZohoCrmServiceContract {
            public function createAccount(array $payload): string
            {
                throw new \RuntimeException('Zoho error');
            }

            public function createDeal(array $payload, string $accountId): string
            {
                return 'DEAL_ID_1';
            }
        });

        $resp = $this->postJson('/api/zoho/submit', [
            'deal_name' => 'Test Deal',
            'deal_stage' => 'Qualification',
            'deal_pipeline' => null,

            'account_name' => 'Test Account',
            'account_website' => 'https://example.com',
            'account_phone' => '+123456',
        ]);
        $resp->assertStatus(422);
        $resp->assertJson([
            'ok' => false,
            'message' => 'Failed to create records',
        ]);

        $resp->assertJsonFragment([
            'error' => 'Zoho error',
        ]);
    }
}
