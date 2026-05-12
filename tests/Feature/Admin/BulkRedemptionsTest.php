<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\RedemptionRequest;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkRedemptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    private function makePendingCashRedemption(int $points = 800): RedemptionRequest
    {
        $agent = Agent::factory()->withWallets()->create();
        $agent->cashWallet->update([
            'available_points' => 0,
            'locked_points'    => $points,
            'lifetime_earned'  => $points,
        ]);

        return RedemptionRequest::create([
            'agent_id'        => $agent->id,
            'type'            => 'cash',
            'points'          => $points,
            'cash_value_usd'  => $points * 2,
            'status'          => 'pending',
            'requested_at'    => now(),
        ]);
    }

    public function test_admin_can_bulk_approve(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $r1 = $this->makePendingCashRedemption(500);
        $r2 = $this->makePendingCashRedemption(700);

        $this->post(route('admin.redemptions.bulk-approve'), ['ids' => [$r1->id, $r2->id]])
            ->assertRedirect();

        $this->assertSame('approved', $r1->fresh()->status);
        $this->assertSame('approved', $r2->fresh()->status);
    }

    public function test_admin_can_bulk_reject_with_shared_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $r1 = $this->makePendingCashRedemption(500);
        $r2 = $this->makePendingCashRedemption(700);

        $this->post(route('admin.redemptions.bulk-reject'), [
            'ids' => [$r1->id, $r2->id],
            'rejection_reason' => 'Bank details missing — please update profile and resubmit.',
        ])->assertRedirect();

        $this->assertSame('rejected', $r1->fresh()->status);
        $this->assertSame('rejected', $r2->fresh()->status);
        $this->assertStringContainsString('Bank details', $r1->fresh()->rejection_reason);
    }

    public function test_bulk_reject_requires_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $r1 = $this->makePendingCashRedemption();

        $this->post(route('admin.redemptions.bulk-reject'), ['ids' => [$r1->id]])
            ->assertSessionHasErrors('rejection_reason');
    }

    public function test_bulk_approve_skips_non_pending_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $pending = $this->makePendingCashRedemption(500);
        $approved = $this->makePendingCashRedemption(800);
        $approved->update(['status' => 'approved']);

        $this->post(route('admin.redemptions.bulk-approve'), ['ids' => [$pending->id, $approved->id]])
            ->assertRedirect();

        $this->assertSame('approved', $pending->fresh()->status);
        // approved one was already approved — should remain approved (not touched)
        $this->assertSame('approved', $approved->fresh()->status);
    }

    public function test_bulk_approve_requires_at_least_one_id(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->post(route('admin.redemptions.bulk-approve'), ['ids' => []])
            ->assertSessionHasErrors('ids');
    }

    public function test_non_admin_cannot_bulk_approve(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $this->actingAs($agent);
        $r1 = $this->makePendingCashRedemption();

        $this->post(route('admin.redemptions.bulk-approve'), ['ids' => [$r1->id]])
            ->assertForbidden();
    }
}
