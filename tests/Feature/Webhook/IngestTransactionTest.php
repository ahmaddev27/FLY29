<?php

namespace Tests\Feature\Webhook;

use App\Models\Agent;
use App\Models\AgentLevel;
use App\Models\CashWalletPoints;
use App\Models\PackageWalletPoints;
use App\Models\PendingTransaction;
use App\Models\PointsHistory;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngestTransactionTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey       = 'test_key';
    private string $webhookSecret = 'test_secret';
    private string $endpoint     = '/api/v1/transactions/ingest';

    protected function setUp(): void
    {
        parent::setUp();

        // Configure test keys
        config([
            'services.main_site.api_key'        => $this->apiKey,
            'services.main_site.webhook_secret' => $this->webhookSecret,
        ]);

        // Seed required tables
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    /* ------------------------------------------------------------------
     | Helpers
     |------------------------------------------------------------------*/

    private function makeAgent(string $externalId = 'AGT-001', string $tier = 'bronze'): Agent
    {
        return Agent::factory()->withWallets()->tier($tier)
            ->create(['external_agent_id' => $externalId]);
    }

    private function postWebhook(array $payload, ?string $secretOverride = null): \Illuminate\Testing\TestResponse
    {
        $rawBody   = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = 'sha256=' . hash_hmac('sha256', $rawBody, $secretOverride ?? $this->webhookSecret);

        return $this->call(
            method: 'POST',
            uri: $this->endpoint,
            parameters: [],
            cookies: [],
            files: [],
            server: [
                'CONTENT_TYPE'     => 'application/json',
                'HTTP_ACCEPT'      => 'application/json',
                'HTTP_X_API_KEY'   => $this->apiKey,
                'HTTP_X_SIGNATURE' => $signature,
            ],
            content: $rawBody,
        );
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'agent_id'         => 'AGT-001',
            'transaction_type' => 'package',
            'amount_usd'       => 1500.00,
            'destination'      => 'Thailand',
            'transaction_date' => '2026-05-11T10:30:00Z',
            'reference_id'     => 'TXN-MAIN-001',
        ], $overrides);
    }

    /* ------------------------------------------------------------------
     | Tests
     |------------------------------------------------------------------*/

    public function test_successful_webhook_credits_points_to_both_wallets(): void
    {
        $this->makeAgent('AGT-001', 'bronze'); // 2 points/package

        $response = $this->postWebhook($this->validPayload());

        $response->assertOk()->assertJson([
            'status'         => 'accepted',
            'points_awarded' => 2,
            'new_balance'    => ['cash' => 2, 'package' => 2],
        ]);

        $this->assertDatabaseCount('transactions', 1);
        $this->assertEquals(2, CashWalletPoints::first()->available_points);
        $this->assertEquals(2, PackageWalletPoints::first()->available_points);
    }

    public function test_duplicate_reference_id_returns_duplicate_ignored(): void
    {
        $this->makeAgent('AGT-001');
        $payload = $this->validPayload();

        $this->postWebhook($payload)->assertOk();
        $second = $this->postWebhook($payload);

        $second->assertOk()->assertJson([
            'status'       => 'duplicate_ignored',
            'reference_id' => $payload['reference_id'],
        ]);

        // No extra rows created
        $this->assertDatabaseCount('transactions', 1);
        $this->assertEquals(2, CashWalletPoints::first()->available_points);
    }

    public function test_invalid_hmac_signature_returns_401(): void
    {
        $this->makeAgent('AGT-001');

        $response = $this->postWebhook($this->validPayload(), secretOverride: 'WRONG_SECRET');

        $response->assertStatus(401)
            ->assertJson(['status' => 'unauthorized', 'error' => 'invalid_signature']);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_missing_api_key_returns_401(): void
    {
        $this->makeAgent('AGT-001');

        $response = $this->withHeaders(['Content-Type' => 'application/json'])
            ->postJson($this->endpoint, $this->validPayload());

        $response->assertStatus(401)
            ->assertJson(['status' => 'unauthorized', 'error' => 'invalid_api_key']);
    }

    public function test_unknown_agent_returns_404(): void
    {
        // No agent created
        $response = $this->postWebhook($this->validPayload(['agent_id' => 'AGT-DOES-NOT-EXIST']));

        $response->assertStatus(404)
            ->assertJson(['status' => 'agent_not_found']);
    }

    public function test_suspended_agent_is_held_for_later(): void
    {
        $agent = $this->makeAgent('AGT-SUSP');
        $agent->user->update(['status' => 'suspended']);

        $response = $this->postWebhook($this->validPayload([
            'agent_id'     => 'AGT-SUSP',
            'reference_id' => 'TXN-SUSP-001',
        ]));

        $response->assertStatus(422)
            ->assertJson([
                'status'           => 'agent_suspended',
                'transaction_held' => true,
            ]);

        $this->assertDatabaseHas('pending_transactions', [
            'external_agent_id' => 'AGT-SUSP',
            'reference_id'      => 'TXN-SUSP-001',
            'reason'            => 'agent_suspended',
        ]);
        // Real transaction NOT created
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_validation_fails_for_invalid_payload(): void
    {
        $this->makeAgent('AGT-001');

        $response = $this->postWebhook($this->validPayload([
            'amount_usd'       => -100,
            'transaction_type' => 'invalid',
        ]));

        $response->assertStatus(422)
            ->assertJson(['status' => 'validation_failed']);
    }

    public function test_tier_upgrade_when_qualifying_threshold_reached(): void
    {
        $agent = $this->makeAgent('AGT-UPG', 'silver'); // already silver

        // Silver→Gold threshold is 20 packages. Insert 19 prior transactions in current window.
        for ($i = 1; $i <= 19; $i++) {
            Transaction::create([
                'agent_id'         => $agent->id,
                'reference_id'     => "TXN-PRIOR-{$i}",
                'transaction_type' => 'package',
                'amount_usd'       => 500,
                'destination'      => 'Thailand',
                'points_awarded'   => 3,
                'config_snapshot'  => [],
                'transaction_date' => now()->startOfMonth()->addDay(),
            ]);
        }

        // The 20th package should push them to Gold.
        $response = $this->postWebhook($this->validPayload([
            'agent_id'     => 'AGT-UPG',
            'reference_id' => 'TXN-PUSH-GOLD',
        ]));

        $response->assertOk()->assertJsonPath('upgraded_to', 'gold');
        $this->assertEquals('gold', $agent->fresh()->current_tier);
        $this->assertDatabaseHas('tier_history', [
            'agent_id'  => $agent->id,
            'from_tier' => 'silver',
            'to_tier'   => 'gold',
            'action'    => 'upgrade',
        ]);
    }

    public function test_amount_based_mode_stores_fraction_in_pending_amount(): void
    {
        SystemSetting::where('key', 'calculation_method')->update(['value' => 'amount_based']);
        // Bronze: 400 USD/point. 1500 USD → floor(1500/400) = 3 points, leftover 300.
        $this->makeAgent('AGT-AMT', 'bronze');

        $response = $this->postWebhook($this->validPayload(['agent_id' => 'AGT-AMT']));

        $response->assertOk()->assertJsonPath('points_awarded', 3);

        $agent = Agent::where('external_agent_id', 'AGT-AMT')->first();
        $this->assertEquals(300.00, (float) $agent->pending_amount);
    }

    public function test_config_snapshot_is_stored_with_transaction(): void
    {
        $this->makeAgent('AGT-001', 'bronze');

        $this->postWebhook($this->validPayload())->assertOk();

        $txn = Transaction::first();
        $this->assertIsArray($txn->config_snapshot);
        $this->assertEquals('package_based', $txn->config_snapshot['calculation_method']);
        $this->assertEquals('bronze', $txn->config_snapshot['tier_at_time']);
        $this->assertEquals(2, $txn->config_snapshot['points_per_package_at_time']);
    }

    public function test_service_transaction_always_grants_exactly_one_point(): void
    {
        $this->makeAgent('AGT-SVC', 'diamond'); // even at diamond, services = 1 pt

        $response = $this->postWebhook($this->validPayload([
            'agent_id'         => 'AGT-SVC',
            'transaction_type' => 'service',
            'amount_usd'       => 50.00,
            'destination'      => null,
            'reference_id'     => 'TXN-SVC-001',
        ]));

        $response->assertOk()->assertJsonPath('points_awarded', 1);
        $this->assertEquals(1, CashWalletPoints::first()->available_points);
    }

    public function test_points_history_records_each_credit(): void
    {
        $this->makeAgent('AGT-001');

        $this->postWebhook($this->validPayload())->assertOk();

        // Two history rows (one per wallet)
        $this->assertDatabaseCount('points_history', 2);
        $this->assertDatabaseHas('points_history', [
            'wallet_type'  => 'cash',
            'points_delta' => 2,
            'source'       => 'transaction',
        ]);
        $this->assertDatabaseHas('points_history', [
            'wallet_type'  => 'package',
            'points_delta' => 2,
            'source'       => 'transaction',
        ]);
    }

    public function test_health_endpoint_is_public(): void
    {
        $response = $this->get('/api/v1/health');
        $response->assertOk()->assertJson(['status' => 'ok']);
    }
}
