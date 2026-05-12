<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\FreePackage;
use App\Models\RedemptionRequest;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    private function approvedCash(int $points = 800): RedemptionRequest
    {
        $agent = Agent::factory()->withWallets()->create();
        // Approved state: points deducted, ready for fulfillment
        $agent->cashWallet->update([
            'available_points'  => 0,
            'locked_points'     => 0,
            'lifetime_earned'   => $points,
            'lifetime_redeemed' => $points,
        ]);

        return RedemptionRequest::create([
            'agent_id'       => $agent->id,
            'type'           => 'cash',
            'points'         => $points,
            'cash_value_usd' => $points * 2,
            'status'         => 'approved',
            'fulfilled'      => false,
            'requested_at'   => now()->subHour(),
            'processed_at'   => now(),
            'processed_by'   => User::factory()->admin()->create()->id,
        ]);
    }

    private function approvedPackage(): RedemptionRequest
    {
        $agent = Agent::factory()->withWallets()->create();
        $package = FreePackage::create([
            'name'            => 'Thailand 7N',
            'destination'     => 'Thailand',
            'points_required' => 5000,
            'is_active'       => true,
        ]);

        return RedemptionRequest::create([
            'agent_id'     => $agent->id,
            'type'         => 'package',
            'points'       => $package->points_required,
            'package_id'   => $package->id,
            'status'       => 'approved',
            'fulfilled'    => false,
            'requested_at' => now()->subHour(),
            'processed_at' => now(),
        ]);
    }

    public function test_admin_can_fulfill_cash_with_reference(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $req = $this->approvedCash();

        $this->post(route('admin.redemptions.fulfill', $req), [
            'fulfillment_reference' => 'TXN-2026-9988',
            'fulfillment_notes'     => 'Sent via SWIFT.',
        ])->assertRedirect();

        $req->refresh();
        $this->assertSame('fulfilled', $req->status);
        $this->assertTrue($req->fulfilled);
        $this->assertSame('TXN-2026-9988', $req->fulfillment_reference);
        $this->assertSame($admin->id, $req->fulfilled_by);
        $this->assertNotNull($req->fulfilled_at);

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'redemption_fulfilled',
            'entity_type' => RedemptionRequest::class,
            'entity_id'   => (string) $req->id,
        ]);
    }

    public function test_fulfilling_cash_requires_reference(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $req = $this->approvedCash();

        $this->post(route('admin.redemptions.fulfill', $req), [])
            ->assertSessionHasErrors('fulfillment_reference');

        $this->assertSame('approved', $req->fresh()->status);
    }

    public function test_admin_can_fulfill_package_without_required_reference(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $req = $this->approvedPackage();

        // Package reference is optional
        $this->post(route('admin.redemptions.fulfill', $req), [
            'fulfillment_notes' => 'Booked with operator XYZ.',
        ])->assertRedirect();

        $this->assertSame('fulfilled', $req->fresh()->status);
    }

    public function test_cannot_fulfill_pending_or_rejected_request(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $pending = $this->approvedCash();
        $pending->update(['status' => 'pending']);

        $this->post(route('admin.redemptions.fulfill', $pending), [
            'fulfillment_reference' => 'X',
        ])->assertSessionHasErrors('action');
    }

    public function test_cannot_fulfill_already_fulfilled_request(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $req = $this->approvedCash();

        // First fulfillment succeeds
        $this->post(route('admin.redemptions.fulfill', $req), [
            'fulfillment_reference' => 'TXN-1',
        ])->assertRedirect();

        // Second attempt errors
        $this->post(route('admin.redemptions.fulfill', $req), [
            'fulfillment_reference' => 'TXN-2',
        ])->assertSessionHasErrors('action');
    }

    public function test_admin_can_reverse_fulfillment(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $req = $this->approvedCash();

        // Fulfill first
        $this->post(route('admin.redemptions.fulfill', $req), [
            'fulfillment_reference' => 'TXN-FAIL',
        ]);
        $this->assertSame('fulfilled', $req->fresh()->status);

        // Reverse
        $this->post(route('admin.redemptions.reverse-fulfillment', $req), [
            'reason' => 'Bank reversed the transfer (account closed).',
        ])->assertRedirect();

        $req->refresh();
        $this->assertSame('approved', $req->status);
        $this->assertFalse($req->fulfilled);
        $this->assertNull($req->fulfillment_reference);
        $this->assertNull($req->fulfilled_at);
        $this->assertNull($req->fulfilled_by);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'redemption_fulfillment_reversed',
        ]);
    }

    public function test_reverse_fulfillment_requires_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $req = $this->approvedCash();

        $this->post(route('admin.redemptions.fulfill', $req), [
            'fulfillment_reference' => 'TXN-1',
        ]);

        $this->post(route('admin.redemptions.reverse-fulfillment', $req), [])
            ->assertSessionHasErrors('reason');
    }

    public function test_cannot_reverse_a_non_fulfilled_request(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $req = $this->approvedCash();
        // Still approved, not fulfilled

        $this->post(route('admin.redemptions.reverse-fulfillment', $req), [
            'reason' => 'Trying to reverse a non-fulfilled.',
        ])->assertSessionHasErrors('action');
    }

    public function test_agent_sees_fulfillment_reference_on_my_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $req = $this->approvedCash();
        $req->update([
            'status'                => 'fulfilled',
            'fulfilled'             => true,
            'fulfilled_at'          => now(),
            'fulfilled_by'          => $admin->id,
            'fulfillment_reference' => 'TXN-SHOWN-TO-AGENT',
        ]);

        $this->actingAs($req->agent->user);

        $this->get('/agent/redemptions')
            ->assertSee('TXN-SHOWN-TO-AGENT')
            ->assertSee('تمّ التنفيذ');
    }
}
