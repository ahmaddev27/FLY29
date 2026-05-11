<?php

namespace Tests\Feature\Agent;

use App\Models\Agent;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\FreePackagesSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class, FreePackagesSeeder::class]);
    }

    private function loginAsAgent(string $tier = 'bronze', int $cashPoints = 0, int $packagePoints = 0): Agent
    {
        $user  = User::factory()->create(['role' => 'agent']);
        $agent = Agent::factory()->for($user)->withWallets()->tier($tier)->create();

        $agent->cashWallet->update(['available_points' => $cashPoints, 'lifetime_earned' => $cashPoints]);
        $agent->packageWallet->update(['available_points' => $packagePoints, 'lifetime_earned' => $packagePoints]);

        $this->actingAs($user);

        return $agent;
    }

    /* ------------------------------------------------------------------ */

    public function test_dashboard_loads_for_agent(): void
    {
        $this->loginAsAgent('bronze', 240, 240);

        $response = $this->get('/agent/dashboard');

        $response->assertOk()
            ->assertSee('تصنيفك الحالي', false)
            ->assertSee('المحفظة النقدية', false)
            ->assertSee('محفظة الباكجات', false)
            ->assertSee('آخر المعاملات', false);
    }

    public function test_dashboard_shows_correct_balances(): void
    {
        $this->loginAsAgent('silver', 1200, 540);

        $response = $this->get('/agent/dashboard');

        $response->assertOk()
            ->assertSee('1,200', false)   // cash available
            ->assertSee('540', false);    // package available
    }

    public function test_dashboard_shows_recent_transactions(): void
    {
        $agent = $this->loginAsAgent('gold', 0, 0);

        Transaction::create([
            'agent_id'         => $agent->id,
            'reference_id'     => 'TXN-VIEW-001',
            'transaction_type' => 'package',
            'amount_usd'       => 1500,
            'destination'      => 'Bangkok',
            'points_awarded'   => 4,
            'config_snapshot'  => ['tier_at_time' => 'gold'],
            'transaction_date' => now(),
        ]);

        $response = $this->get('/agent/dashboard');

        $response->assertOk()
            ->assertSee('TXN-VIEW-001', false)
            ->assertSee('Bangkok', false)
            ->assertSee('+4', false);
    }

    public function test_dashboard_shows_empty_state_for_brand_new_agent(): void
    {
        $this->loginAsAgent('bronze', 0, 0);

        $response = $this->get('/agent/dashboard');

        $response->assertOk()
            ->assertSee('مرحباً بك في برنامج ولاء 29FLY!', false);
    }

    public function test_dashboard_shows_tier_progress(): void
    {
        $agent = $this->loginAsAgent('bronze', 0, 0);

        // Add 5 package transactions (silver threshold = 10)
        for ($i = 1; $i <= 5; $i++) {
            Transaction::create([
                'agent_id'         => $agent->id,
                'reference_id'     => "TXN-PROG-{$i}",
                'transaction_type' => 'package',
                'amount_usd'       => 500,
                'points_awarded'   => 2,
                'config_snapshot'  => [],
                'transaction_date' => now()->startOfMonth()->addDay(),
            ]);
        }

        $response = $this->get('/agent/dashboard');

        $response->assertOk()
            ->assertSee('5', false)         // packages count
            ->assertSee('10', false);       // silver threshold
    }

    public function test_dashboard_is_forbidden_for_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get('/agent/dashboard');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/agent/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_tier_upgrade_reflects_immediately(): void
    {
        $agent = $this->loginAsAgent('silver', 0, 0);

        // Simulate a tier upgrade via the TierService (or manually)
        $agent->update(['current_tier' => 'gold', 'tier_valid_until' => now()->addDays(30)]);

        $response = $this->get('/agent/dashboard');

        $response->assertOk()
            ->assertSee('ذهبي', false);
    }

    public function test_profile_page_loads(): void
    {
        $this->loginAsAgent();

        $response = $this->get('/agent/profile');

        $response->assertOk()
            ->assertSee('البيانات الشخصية', false)
            ->assertSee('تغيير كلمة المرور', false);
    }

    public function test_profile_update_works(): void
    {
        $this->loginAsAgent();

        $response = $this->put('/agent/profile', [
            'full_name' => 'اسم جديد',
            'phone'     => '+966500000000',
            'city'      => 'جدة',
        ]);

        $response->assertRedirect();
        $this->assertEquals('اسم جديد', auth()->user()->fresh()->full_name);
        $this->assertEquals('جدة', auth()->user()->agent->fresh()->city);
    }

    public function test_notification_preferences_page_loads(): void
    {
        $this->loginAsAgent();

        $response = $this->get('/agent/notification-preferences');

        $response->assertOk()
            ->assertSee('ترقية التصنيف', false)
            ->assertSee('بريد', false)
            ->assertSee('SMS', false);
    }

    public function test_notification_preferences_update(): void
    {
        $this->loginAsAgent();

        $response = $this->put('/agent/notification-preferences', [
            'preferences' => [
                'tier_upgraded'  => ['email_enabled' => '1', 'in_app_enabled' => '1'],
                'points_earned'  => ['in_app_enabled' => '1'], // email + sms off
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('user_notification_preferences', [
            'notification_type' => 'tier_upgraded',
            'email_enabled'     => 1,
            'in_app_enabled'    => 1,
        ]);
        $this->assertDatabaseHas('user_notification_preferences', [
            'notification_type' => 'points_earned',
            'email_enabled'     => 0,
            'in_app_enabled'    => 1,
        ]);
    }
}
