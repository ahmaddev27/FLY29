<?php

namespace Tests\Feature\Console;

use App\Models\Agent;
use App\Models\ApiLog;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CronCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    /* --- tiers:evaluate ----------------------------------------------- */

    public function test_evaluate_tiers_downgrades_expired_gold_agent_with_no_packages(): void
    {
        // Gold agent with no packages this month, tier_valid_until in the past
        $agent = Agent::factory()->withWallets()->tier('gold')->create([
            'tier_valid_until' => now()->subDay(),
        ]);

        $this->artisan('tiers:evaluate')->assertSuccessful();

        $this->assertSame('bronze', $agent->fresh()->current_tier);
        $this->assertDatabaseHas('tier_history', [
            'agent_id'  => $agent->id,
            'from_tier' => 'gold',
            'to_tier'   => 'bronze',
            'action'    => 'downgrade',
        ]);
    }

    public function test_evaluate_tiers_dry_run_does_not_modify_agents(): void
    {
        $agent = Agent::factory()->withWallets()->tier('gold')->create([
            'tier_valid_until' => now()->subDay(),
        ]);

        $this->artisan('tiers:evaluate', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('gold', $agent->fresh()->current_tier);
        $this->assertDatabaseMissing('tier_history', ['agent_id' => $agent->id]);
    }

    /* --- tokens:cleanup ----------------------------------------------- */

    public function test_tokens_cleanup_removes_expired_password_resets(): void
    {
        DB::table('password_reset_tokens')->insert([
            'email'      => 'old@example.com',
            'token'      => 'x',
            'created_at' => now()->subHours(5),
        ]);
        DB::table('password_reset_tokens')->insert([
            'email'      => 'fresh@example.com',
            'token'      => 'y',
            'created_at' => now()->subMinutes(10),
        ]);

        $this->artisan('tokens:cleanup')->assertSuccessful();

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'old@example.com']);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'fresh@example.com']);
    }

    /* --- logs:archive ------------------------------------------------- */

    public function test_logs_archive_prunes_old_api_logs(): void
    {
        // Old log past 90-day retention
        $oldId = ApiLog::create([
            'method' => 'POST', 'endpoint' => '/old',
            'response_code' => 200, 'status' => 'success',
        ])->id;
        DB::table('api_logs')->where('id', $oldId)->update(['created_at' => now()->subDays(120)]);

        // Recent log — should survive
        $newId = ApiLog::create([
            'method' => 'POST', 'endpoint' => '/new',
            'response_code' => 200, 'status' => 'success',
        ])->id;

        $this->artisan('logs:archive')->assertSuccessful();

        $this->assertDatabaseMissing('api_logs', ['id' => $oldId]);
        $this->assertDatabaseHas('api_logs', ['id' => $newId]);
    }

    /* --- transactions:reconcile --------------------------------------- */

    public function test_reconcile_logs_an_api_log_entry(): void
    {
        // No Main Site config → command should fail gracefully and log
        $this->artisan('transactions:reconcile', ['date' => now()->subDay()->toDateString()])
             ->assertFailed();

        $this->assertDatabaseHas('api_logs', [
            'endpoint' => 'transactions:reconcile',
        ]);
    }
}
