<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\RedemptionRequest;
use App\Models\TierHistory;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        return $admin;
    }

    public function test_non_admin_cannot_access_reports(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $this->actingAs($agent);
        $this->get('/admin/reports')->assertForbidden();
    }

    public function test_admin_can_view_reports_landing(): void
    {
        $this->actingAsAdmin();
        $this->get('/admin/reports')
            ->assertOk()
            ->assertSee('مركز التقارير');
    }

    public function test_sales_report_aggregates_correctly(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create();

        Transaction::create([
            'agent_id' => $agent->id, 'reference_id' => 'X1',
            'transaction_type' => 'package', 'amount_usd' => 1000,
            'points_awarded' => 2, 'transaction_date' => now()->subDays(2),
            'created_by_source' => 'webhook', 'config_snapshot' => [],
        ]);
        Transaction::create([
            'agent_id' => $agent->id, 'reference_id' => 'X2',
            'transaction_type' => 'service', 'amount_usd' => 500,
            'points_awarded' => 1, 'transaction_date' => now()->subDays(1),
            'created_by_source' => 'webhook', 'config_snapshot' => [],
        ]);

        $response = $this->get('/admin/reports/sales');
        $response->assertOk()
                 ->assertSee('1,500')  // total revenue
                 ->assertSee('تقرير المبيعات');
    }

    public function test_top_agents_report_ranks_by_revenue(): void
    {
        $this->actingAsAdmin();

        $a = Agent::factory()->withWallets()->create(['business_name' => 'High Revenue Co']);
        $b = Agent::factory()->withWallets()->create(['business_name' => 'Low Revenue Co']);

        Transaction::create([
            'agent_id' => $a->id, 'reference_id' => 'A1',
            'transaction_type' => 'package', 'amount_usd' => 5000,
            'points_awarded' => 10, 'transaction_date' => now()->subDay(),
            'created_by_source' => 'webhook', 'config_snapshot' => [],
        ]);
        Transaction::create([
            'agent_id' => $b->id, 'reference_id' => 'B1',
            'transaction_type' => 'package', 'amount_usd' => 100,
            'points_awarded' => 1, 'transaction_date' => now()->subDay(),
            'created_by_source' => 'webhook', 'config_snapshot' => [],
        ]);

        $response = $this->get('/admin/reports/top-agents');

        // High revenue should appear before low revenue
        $body = $response->getContent();
        $posHigh = strpos($body, 'High Revenue Co');
        $posLow  = strpos($body, 'Low Revenue Co');

        $this->assertNotFalse($posHigh);
        $this->assertNotFalse($posLow);
        $this->assertLessThan($posLow, $posHigh, 'High Revenue Co should appear before Low Revenue Co');
    }

    public function test_tiers_report_shows_current_distribution(): void
    {
        $this->actingAsAdmin();
        Agent::factory()->withWallets()->tier('gold')->count(3)->create();
        Agent::factory()->withWallets()->tier('bronze')->count(7)->create();

        $this->get('/admin/reports/tiers')
            ->assertOk()
            ->assertSee('التوزيع الحالي');
    }

    public function test_redemptions_report_aggregates_by_status(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create();

        RedemptionRequest::create([
            'agent_id' => $agent->id, 'type' => 'cash', 'points' => 800,
            'cash_value_usd' => 1600, 'status' => 'pending', 'requested_at' => now()->subDay(),
        ]);
        RedemptionRequest::create([
            'agent_id' => $agent->id, 'type' => 'cash', 'points' => 1000,
            'cash_value_usd' => 2000, 'status' => 'approved', 'requested_at' => now()->subDay(),
        ]);

        $this->get('/admin/reports/redemptions')
            ->assertOk()
            ->assertSee('تقرير الاستبدالات');
    }

    public function test_top_agents_excel_export_works(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create();
        Transaction::create([
            'agent_id' => $agent->id, 'reference_id' => 'XL1',
            'transaction_type' => 'package', 'amount_usd' => 500,
            'points_awarded' => 1, 'transaction_date' => now()->subDay(),
            'created_by_source' => 'webhook', 'config_snapshot' => [],
        ]);

        $response = $this->get('/admin/reports/top-agents/xlsx');
        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }
}
