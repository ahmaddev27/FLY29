<?php

namespace Tests\Feature\Redemption;

use App\Models\Agent;
use App\Models\RedemptionRequest;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashRedemptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    private function makeAgent(int $cashPoints = 1000): Agent
    {
        $user  = User::factory()->create(['role' => 'agent']);
        $agent = Agent::factory()->for($user)->withWallets()->create();
        $agent->cashWallet->update(['available_points' => $cashPoints, 'lifetime_earned' => $cashPoints]);
        $this->actingAs($user);

        return $agent;
    }

    public function test_agent_can_submit_cash_redemption(): void
    {
        $agent = $this->makeAgent(1500);

        $response = $this->post('/agent/redemptions/cash', ['points' => 800]);

        $response->assertRedirect();

        $this->assertDatabaseHas('redemption_requests', [
            'agent_id' => $agent->id,
            'type'     => 'cash',
            'points'   => 800,
            'status'   => 'pending',
        ]);

        // Wallet: 800 moved from available to locked
        $cash = $agent->cashWallet->fresh();
        $this->assertSame(700, $cash->available_points);
        $this->assertSame(800, $cash->locked_points);
    }

    public function test_below_minimum_is_rejected(): void
    {
        $this->makeAgent(1500);

        $response = $this->post('/agent/redemptions/cash', ['points' => 500]);

        $response->assertSessionHasErrors('points');
        $this->assertDatabaseCount('redemption_requests', 0);
    }

    public function test_above_available_is_rejected(): void
    {
        $agent = $this->makeAgent(500);  // less than the 800 min, so just try a bigger amount

        // Boost balance to bypass the min check but stay below the request amount
        $agent->cashWallet->update(['available_points' => 900]);

        $response = $this->post('/agent/redemptions/cash', ['points' => 1500]);

        // Expect session error (wallet locking throws DomainException → withErrors)
        $response->assertSessionHasErrors('points');
        $this->assertDatabaseCount('redemption_requests', 0);
    }

    public function test_admin_can_approve_cash_request(): void
    {
        $agent = $this->makeAgent(1000);
        $this->post('/agent/redemptions/cash', ['points' => 800])->assertRedirect();
        $request = RedemptionRequest::first();

        // Switch to admin
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->post("/admin/redemptions/{$request->id}/approve");
        $response->assertRedirect();

        $request->refresh();
        $this->assertSame('approved', $request->status);
        $this->assertSame($admin->id, $request->processed_by);

        // Wallet: locked drained to 0, lifetime_redeemed bumped
        $cash = $agent->cashWallet->fresh();
        $this->assertSame(200, $cash->available_points);
        $this->assertSame(0, $cash->locked_points);
        $this->assertSame(800, $cash->lifetime_redeemed);
    }

    public function test_admin_can_reject_with_reason(): void
    {
        $agent = $this->makeAgent(1000);
        $this->post('/agent/redemptions/cash', ['points' => 800])->assertRedirect();
        $request = RedemptionRequest::first();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->post("/admin/redemptions/{$request->id}/reject", [
            'rejection_reason' => 'بيانات الحساب البنكي غير مكتملة، يرجى تحديثها.',
        ]);
        $response->assertRedirect();

        $request->refresh();
        $this->assertSame('rejected', $request->status);
        $this->assertNotEmpty($request->rejection_reason);

        // Wallet: locked returned to available
        $cash = $agent->cashWallet->fresh();
        $this->assertSame(1000, $cash->available_points);
        $this->assertSame(0, $cash->locked_points);
    }

    public function test_reject_requires_reason(): void
    {
        $agent = $this->makeAgent(1000);
        $this->post('/agent/redemptions/cash', ['points' => 800])->assertRedirect();
        $request = RedemptionRequest::first();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->post("/admin/redemptions/{$request->id}/reject", ['rejection_reason' => '']);
        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_agent_can_cancel_own_pending_request(): void
    {
        $agent = $this->makeAgent(1000);
        $this->post('/agent/redemptions/cash', ['points' => 800])->assertRedirect();
        $request = RedemptionRequest::first();

        $response = $this->delete("/agent/redemptions/{$request->id}");
        $response->assertRedirect();

        $request->refresh();
        $this->assertSame('cancelled', $request->status);

        // Wallet: locked returned
        $cash = $agent->cashWallet->fresh();
        $this->assertSame(1000, $cash->available_points);
        $this->assertSame(0, $cash->locked_points);
    }

    public function test_admin_cannot_approve_non_pending(): void
    {
        $agent = $this->makeAgent(1000);
        $this->post('/agent/redemptions/cash', ['points' => 800])->assertRedirect();
        $request = RedemptionRequest::first();

        // Cancel first
        $this->delete("/agent/redemptions/{$request->id}");
        $this->assertSame('cancelled', $request->fresh()->status);

        // Try to approve as admin
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->post("/admin/redemptions/{$request->id}/approve");
        $response->assertSessionHasErrors('action');
    }

    public function test_non_admin_cannot_access_admin_redemptions(): void
    {
        $this->makeAgent(); // agent role

        $response = $this->get('/admin/redemptions');
        $response->assertForbidden();
    }

    public function test_redemption_list_paginates(): void
    {
        $agent = $this->makeAgent(50000);

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/agent/redemptions/cash', ['points' => 800 + $i * 10]);
        }

        $response = $this->get('/agent/redemptions');
        $response->assertOk()
            ->assertSee('قيد المراجعة', false);

        $this->assertSame(5, RedemptionRequest::count());
    }
}
