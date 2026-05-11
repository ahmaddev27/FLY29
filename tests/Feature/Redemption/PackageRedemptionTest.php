<?php

namespace Tests\Feature\Redemption;

use App\Models\Agent;
use App\Models\FreePackage;
use App\Models\RedemptionRequest;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageRedemptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    private function makeAgent(int $packagePoints = 1000): Agent
    {
        $user  = User::factory()->create(['role' => 'agent']);
        $agent = Agent::factory()->for($user)->withWallets()->create();
        $agent->packageWallet->update(['available_points' => $packagePoints, 'lifetime_earned' => $packagePoints]);
        $this->actingAs($user);

        return $agent;
    }

    private function makePackage(array $attrs = []): FreePackage
    {
        return FreePackage::create(array_merge([
            'name'            => 'باكج تايلاند',
            'destination'     => 'Thailand',
            'points_required' => 1000,
            'duration_days'   => 7,
            'is_active'       => true,
            'display_order'   => 1,
        ], $attrs));
    }

    /* ------------------------------------------------------------------ */

    public function test_agent_can_redeem_affordable_package(): void
    {
        $agent   = $this->makeAgent(1500);
        $package = $this->makePackage(['points_required' => 1000]);

        $response = $this->post("/agent/redemptions/packages/{$package->id}/redeem");

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('redemption_requests', [
            'agent_id'   => $agent->id,
            'type'       => 'package',
            'package_id' => $package->id,
            'points'     => 1000,
            'status'     => 'approved', // instant approval
            'fulfilled'  => 0,
        ]);

        // Wallet: 1000 deducted (no locking — instant debit)
        $pkg = $agent->packageWallet->fresh();
        $this->assertSame(500, $pkg->available_points);
        $this->assertSame(0, $pkg->locked_points);
        $this->assertSame(1000, $pkg->lifetime_redeemed);
    }

    public function test_insufficient_balance_is_rejected(): void
    {
        $this->makeAgent(500); // less than required 1000
        $package = $this->makePackage(['points_required' => 1000]);

        $response = $this->post("/agent/redemptions/packages/{$package->id}/redeem");

        $response->assertSessionHasErrors('redeem');
        $this->assertDatabaseCount('redemption_requests', 0);
    }

    public function test_inactive_package_cannot_be_redeemed(): void
    {
        $this->makeAgent(2000);
        $package = $this->makePackage(['is_active' => false, 'points_required' => 1000]);

        $response = $this->post("/agent/redemptions/packages/{$package->id}/redeem");

        $response->assertSessionHasErrors('redeem');
        $this->assertDatabaseCount('redemption_requests', 0);
    }

    public function test_expired_package_cannot_be_redeemed(): void
    {
        $this->makeAgent(2000);
        $package = $this->makePackage([
            'points_required' => 1000,
            'valid_until'     => now()->subDay(),
        ]);

        $response = $this->post("/agent/redemptions/packages/{$package->id}/redeem");

        $response->assertSessionHasErrors('redeem');
        $this->assertDatabaseCount('redemption_requests', 0);
    }

    public function test_packages_list_shows_only_active_and_marks_affordable(): void
    {
        $this->makeAgent(1200);

        $cheap  = $this->makePackage(['name' => 'Cheap',  'points_required' => 500]);
        $expensive = $this->makePackage(['name' => 'Expensive', 'points_required' => 5000]);
        $hidden = $this->makePackage(['name' => 'Hidden', 'is_active' => false]);

        $response = $this->get('/agent/redemptions/packages');

        $response->assertOk()
            ->assertSee('Cheap', false)
            ->assertSee('Expensive', false)
            ->assertDontSee('Hidden', false);

        // Cheap should be affordable, Expensive should not
        $response->assertSee('استبدال الآن', false);       // affordable button
        $response->assertSee('رصيد غير كافٍ', false);      // unaffordable button
    }

    public function test_admin_can_create_a_package(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->post('/admin/packages', [
            'name'            => 'Test Package',
            'destination'     => 'Egypt',
            'points_required' => 2000,
            'duration_days'   => 5,
            'description'     => 'A nice trip',
            'is_active'       => '1',
            'display_order'   => 1,
        ]);

        $response->assertRedirect('/admin/packages');
        $this->assertDatabaseHas('free_packages', [
            'name'            => 'Test Package',
            'destination'     => 'Egypt',
            'points_required' => 2000,
            'is_active'       => 1,
        ]);
    }

    public function test_admin_can_toggle_package_active_state(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $package = $this->makePackage(['is_active' => true]);

        $this->patch("/admin/packages/{$package->id}/toggle")->assertRedirect();
        $this->assertFalse($package->fresh()->is_active);

        $this->patch("/admin/packages/{$package->id}/toggle")->assertRedirect();
        $this->assertTrue($package->fresh()->is_active);
    }

    public function test_admin_can_delete_a_package(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $package = $this->makePackage();

        $this->delete("/admin/packages/{$package->id}")->assertRedirect();
        $this->assertDatabaseMissing('free_packages', ['id' => $package->id]);
    }

    public function test_non_admin_cannot_access_admin_packages(): void
    {
        $this->makeAgent(); // role = agent

        $this->get('/admin/packages')->assertForbidden();
        $this->post('/admin/packages', [
            'name' => 'X', 'destination' => 'Y', 'points_required' => 100,
        ])->assertForbidden();
    }

    public function test_redeeming_credits_points_history(): void
    {
        $agent   = $this->makeAgent(2000);
        $package = $this->makePackage(['points_required' => 1500]);

        $this->post("/agent/redemptions/packages/{$package->id}/redeem")->assertRedirect();

        $this->assertDatabaseHas('points_history', [
            'agent_id'     => $agent->id,
            'wallet_type'  => 'package',
            'points_delta' => -1500,
            'source'       => 'redemption',
        ]);
    }
}
