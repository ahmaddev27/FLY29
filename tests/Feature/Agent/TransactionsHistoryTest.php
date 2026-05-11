<?php

namespace Tests\Feature\Agent;

use App\Models\Agent;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionsHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    private function makeAgent(): Agent
    {
        $user  = User::factory()->create(['role' => 'agent']);
        $agent = Agent::factory()->for($user)->withWallets()->create();
        $this->actingAs($user);

        return $agent;
    }

    private function seedTxns(Agent $agent, int $count = 5, string $type = 'package', ?string $when = null): void
    {
        $when ??= '-1 day';
        for ($i = 1; $i <= $count; $i++) {
            Transaction::create([
                'agent_id'         => $agent->id,
                'reference_id'     => "TXN-T-{$type}-{$i}",
                'transaction_type' => $type,
                'amount_usd'       => 100 * $i,
                'destination'      => $type === 'package' ? 'Thailand' : null,
                'points_awarded'   => $type === 'package' ? 2 : 1,
                'config_snapshot'  => [],
                'transaction_date' => date('Y-m-d H:i:s', strtotime($when . " -{$i} hour")),
            ]);
        }
    }

    /* ------------------------------------------------------------------ */

    public function test_history_page_loads_with_summary(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 3, 'package');
        $this->seedTxns($agent, 2, 'service');

        $response = $this->get('/agent/transactions');

        $response->assertOk()
            ->assertSee('سجل النقاط', false)
            ->assertSee('عدد المعاملات', false)
            ->assertSee('5', false); // total count
    }

    public function test_type_filter_works(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 3, 'package');
        $this->seedTxns($agent, 2, 'service');

        $response = $this->get('/agent/transactions?type=package');

        $response->assertOk()
            ->assertSee('TXN-T-package-1', false)
            ->assertDontSee('TXN-T-service-1', false);
    }

    public function test_date_range_filter_works(): void
    {
        $agent = $this->makeAgent();

        // 2 transactions today
        Transaction::create([
            'agent_id' => $agent->id, 'reference_id' => 'TXN-TODAY-1',
            'transaction_type' => 'package', 'amount_usd' => 500,
            'points_awarded' => 2, 'config_snapshot' => [],
            'transaction_date' => now(),
        ]);

        // 1 transaction 60 days ago
        Transaction::create([
            'agent_id' => $agent->id, 'reference_id' => 'TXN-OLD-1',
            'transaction_type' => 'package', 'amount_usd' => 500,
            'points_awarded' => 2, 'config_snapshot' => [],
            'transaction_date' => now()->subDays(60),
        ]);

        $from = now()->subDays(7)->format('Y-m-d');
        $response = $this->get("/agent/transactions?from={$from}");

        $response->assertOk()
            ->assertSee('TXN-TODAY-1', false)
            ->assertDontSee('TXN-OLD-1', false);
    }

    public function test_reference_search_works(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 3, 'package');

        $response = $this->get('/agent/transactions?reference=TXN-T-package-2');

        $response->assertOk()
            ->assertSee('TXN-T-package-2', false)
            ->assertDontSee('TXN-T-package-1', false)
            ->assertDontSee('TXN-T-package-3', false);
    }

    public function test_pagination_works(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 60, 'package');

        $response = $this->get('/agent/transactions');

        // Should see first 50 only on page 1
        $response->assertOk();
        $this->assertEquals(60, Transaction::count());
    }

    public function test_csv_export_streams_correct_headers(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 3, 'package');

        $response = $this->get('/agent/transactions/export/csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('التاريخ', $content);
        $this->assertStringContainsString('TXN-T-package-1', $content);
    }

    public function test_excel_export_returns_xlsx(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 2, 'package');

        $response = $this->get('/agent/transactions/export/excel');

        $response->assertOk();
        $contentType = $response->headers->get('Content-Type');
        $this->assertTrue(
            str_contains($contentType, 'spreadsheetml') || str_contains($contentType, 'octet-stream'),
            "Unexpected content type: {$contentType}",
        );
    }

    public function test_pdf_export_returns_pdf(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 2, 'package');

        $response = $this->get('/agent/transactions/export/pdf');

        $response->assertOk();
        $this->assertStringContainsString('pdf', strtolower($response->headers->get('Content-Type')));
    }

    public function test_empty_state_shown_when_no_results(): void
    {
        $this->makeAgent();

        $response = $this->get('/agent/transactions?reference=NOMATCH');

        $response->assertOk()
            ->assertSee('لا توجد معاملات تطابق الفلاتر', false);
    }

    public function test_only_own_transactions_visible(): void
    {
        $agent = $this->makeAgent();
        $this->seedTxns($agent, 2, 'package');

        // Another agent's transaction
        $other = Agent::factory()->withWallets()->create();
        Transaction::create([
            'agent_id' => $other->id, 'reference_id' => 'TXN-OTHER-1',
            'transaction_type' => 'package', 'amount_usd' => 500,
            'points_awarded' => 2, 'config_snapshot' => [],
            'transaction_date' => now(),
        ]);

        $response = $this->get('/agent/transactions');

        $response->assertOk()
            ->assertSee('TXN-T-package-1', false)
            ->assertDontSee('TXN-OTHER-1', false);
    }
}
