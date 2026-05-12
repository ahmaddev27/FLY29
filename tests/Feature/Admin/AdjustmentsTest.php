<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\PendingAdjustment;
use App\Models\User;
use App\Services\AdjustmentService;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdjustmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    private function makeAgent(int $cash = 1000, int $package = 1000): Agent
    {
        $agent = Agent::factory()->withWallets()->create();
        $agent->cashWallet->update(['available_points' => $cash, 'lifetime_earned' => $cash]);
        $agent->packageWallet->update(['available_points' => $package, 'lifetime_earned' => $package]);

        return $agent;
    }

    public function test_small_adjustment_applies_immediately(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $agent = $this->makeAgent(cash: 500);

        $this->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 200,
            'reason'       => 'Goodwill bonus for the agent',
        ])->assertRedirect();

        $this->assertSame(700, $agent->cashWallet->fresh()->available_points);
        $this->assertDatabaseCount('pending_adjustments', 0);
    }

    public function test_negative_adjustment_debits_wallet(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $agent = $this->makeAgent(cash: 500);

        $this->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => -150,
            'reason'       => 'Correction of duplicate credit',
        ])->assertRedirect();

        $this->assertSame(350, $agent->cashWallet->fresh()->available_points);
    }

    public function test_large_adjustment_queues_for_dual_approval(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $agent = $this->makeAgent(cash: 500);

        $this->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 800, // > default threshold of 500
            'reason'       => 'Marketing campaign bonus',
        ])->assertRedirect();

        // Wallet unchanged
        $this->assertSame(500, $agent->cashWallet->fresh()->available_points);

        // Queued
        $this->assertDatabaseHas('pending_adjustments', [
            'agent_id'     => $agent->id,
            'wallet_type'  => 'cash',
            'points_delta' => 800,
            'status'       => 'pending',
        ]);
    }

    public function test_super_admin_can_approve_pending_adjustment(): void
    {
        $requester = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $agent = $this->makeAgent(cash: 500);

        // Step 1: regular admin queues it
        $this->actingAs($requester)->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 1000,
            'reason'       => 'Q1 bonus campaign payout',
        ])->assertRedirect();

        $pending = PendingAdjustment::first();
        $this->assertSame('pending', $pending->status);

        // Step 2: super admin approves
        $this->actingAs($superAdmin)
            ->post(route('admin.adjustments.approve', $pending), ['notes' => 'Verified with manager.'])
            ->assertRedirect();

        $this->assertSame('approved', $pending->fresh()->status);
        $this->assertSame($superAdmin->id, $pending->fresh()->approved_by);
        $this->assertSame(1500, $agent->cashWallet->fresh()->available_points);
    }

    public function test_super_admin_cannot_approve_own_request(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $agent = $this->makeAgent(cash: 500);

        // Super admin queues the adjustment themselves
        $this->actingAs($superAdmin)->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 900,
            'reason'       => 'Self-requested adjustment',
        ])->assertRedirect();

        $pending = PendingAdjustment::first();

        // Try to self-approve
        $this->actingAs($superAdmin)
            ->post(route('admin.adjustments.approve', $pending))
            ->assertSessionHasErrors('adjustment');

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame(500, $agent->cashWallet->fresh()->available_points);
    }

    public function test_regular_admin_cannot_approve(): void
    {
        $requester  = User::factory()->admin()->create();
        $secondAdmin = User::factory()->admin()->create();
        $agent = $this->makeAgent(cash: 500);

        $this->actingAs($requester)->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 900,
            'reason'       => 'Big bonus request',
        ]);

        $pending = PendingAdjustment::first();

        $this->actingAs($secondAdmin)
            ->post(route('admin.adjustments.approve', $pending))
            ->assertForbidden();
    }

    public function test_super_admin_can_reject_pending_adjustment(): void
    {
        $requester  = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $agent = $this->makeAgent(cash: 500);

        $this->actingAs($requester)->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 1000,
            'reason'       => 'Big bonus',
        ]);

        $pending = PendingAdjustment::first();

        $this->actingAs($superAdmin)
            ->post(route('admin.adjustments.reject', $pending), ['notes' => 'Not justified.'])
            ->assertRedirect();

        $this->assertSame('rejected', $pending->fresh()->status);
        $this->assertSame(500, $agent->cashWallet->fresh()->available_points);
    }

    public function test_requester_can_cancel_own_pending_adjustment(): void
    {
        $admin = User::factory()->admin()->create();
        $agent = $this->makeAgent();

        $this->actingAs($admin)->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 800,
            'reason'       => 'Pending bonus',
        ]);

        $pending = PendingAdjustment::first();

        $this->actingAs($admin)
            ->post(route('admin.adjustments.cancel', $pending))
            ->assertRedirect();

        $this->assertSame('cancelled', $pending->fresh()->status);
    }

    public function test_other_admin_cannot_cancel_someone_elses_request(): void
    {
        $requester = User::factory()->admin()->create();
        $other     = User::factory()->admin()->create();
        $agent = $this->makeAgent();

        $this->actingAs($requester)->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 700,
            'reason'       => 'Bonus',
        ]);

        $pending = PendingAdjustment::first();

        $this->actingAs($other)
            ->post(route('admin.adjustments.cancel', $pending))
            ->assertSessionHasErrors('adjustment');

        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_threshold_change_takes_effect_immediately(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $agent = $this->makeAgent();

        // Tighten the threshold to 100 via the SettingsService
        app(\App\Services\SettingsService::class)->set('dual_approval_threshold', 100);

        $this->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 200, // now > new threshold
            'reason'       => 'Small adjust under new policy',
        ])->assertRedirect();

        $this->assertDatabaseHas('pending_adjustments', ['points_delta' => 200, 'status' => 'pending']);
    }

    public function test_zero_delta_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $agent = $this->makeAgent();

        $this->post(route('admin.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 0,
            'reason'       => 'invalid',
        ])->assertSessionHasErrors('points_delta');
    }

    public function test_insufficient_balance_blocks_direct_debit(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $agent = $this->makeAgent(cash: 100);

        $this->expectException(\DomainException::class);

        app(AdjustmentService::class)->request(
            agent: $agent,
            wallet: 'cash',
            delta: -200,
            reason: 'over-debit',
            requestedBy: $admin,
        );
    }
}
