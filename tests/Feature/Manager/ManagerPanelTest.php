<?php

namespace Tests\Feature\Manager;

use App\Models\Agent;
use App\Models\PendingAdjustment;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    /* ------------------------------------------------------------------ */
    /* Access control                                                     */
    /* ------------------------------------------------------------------ */

    public function test_non_manager_cannot_access_manager_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/manager/dashboard')->assertForbidden();
    }

    public function test_manager_can_access_dashboard(): void
    {
        $manager = User::factory()->accountManager()->create();
        $this->actingAs($manager);

        $this->get('/manager/dashboard')
            ->assertOk()
            ->assertSee('لوحة مدير الحسابات');
    }

    /* ------------------------------------------------------------------ */
    /* Row-level security on agents                                        */
    /* ------------------------------------------------------------------ */

    public function test_manager_only_sees_their_own_assigned_agents(): void
    {
        $manager       = User::factory()->accountManager()->create();
        $otherManager  = User::factory()->accountManager()->create();

        $mine = Agent::factory()->withWallets()->create([
            'account_manager_id' => $manager->id,
            'business_name'      => 'My Assigned Co',
        ]);
        $notMine = Agent::factory()->withWallets()->create([
            'account_manager_id' => $otherManager->id,
            'business_name'      => 'Other Manager Co',
        ]);
        $unassigned = Agent::factory()->withWallets()->create([
            'account_manager_id' => null,
            'business_name'      => 'Unassigned Co',
        ]);

        $this->actingAs($manager);

        $this->get('/manager/agents')
            ->assertOk()
            ->assertSee('My Assigned Co')
            ->assertDontSee('Other Manager Co')
            ->assertDontSee('Unassigned Co');
    }

    public function test_manager_cannot_view_someone_elses_agent_profile(): void
    {
        $manager      = User::factory()->accountManager()->create();
        $otherManager = User::factory()->accountManager()->create();

        $foreignAgent = Agent::factory()->withWallets()->create([
            'account_manager_id' => $otherManager->id,
        ]);

        $this->actingAs($manager)
            ->get("/manager/agents/{$foreignAgent->id}")
            ->assertNotFound();
    }

    public function test_manager_can_view_their_own_agent_profile(): void
    {
        $manager = User::factory()->accountManager()->create();
        $agent = Agent::factory()->withWallets()->create([
            'account_manager_id' => $manager->id,
            'business_name'      => 'Profile Test Co',
        ]);

        $this->actingAs($manager)
            ->get("/manager/agents/{$agent->id}")
            ->assertOk()
            ->assertSee('Profile Test Co');
    }

    /* ------------------------------------------------------------------ */
    /* Suggest adjustment                                                  */
    /* ------------------------------------------------------------------ */

    public function test_manager_can_suggest_adjustment_for_their_agent(): void
    {
        $manager = User::factory()->accountManager()->create();
        $agent = Agent::factory()->withWallets()->create([
            'account_manager_id' => $manager->id,
        ]);

        $this->actingAs($manager);
        $agent->cashWallet->update(['available_points' => 500]);

        $this->post(route('manager.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 200,
            'reason'       => 'Performance bonus for great service.',
        ])->assertRedirect();

        $this->assertDatabaseHas('pending_adjustments', [
            'agent_id'     => $agent->id,
            'wallet_type'  => 'cash',
            'points_delta' => 200,
            'status'       => 'pending',
            'requested_by' => $manager->id,
        ]);

        // Wallet unchanged — manager's suggestion always queues, never applies
        $this->assertSame(500, $agent->cashWallet->fresh()->available_points);
    }

    public function test_manager_cannot_suggest_for_someone_elses_agent(): void
    {
        $manager      = User::factory()->accountManager()->create();
        $otherManager = User::factory()->accountManager()->create();
        $foreignAgent = Agent::factory()->withWallets()->create([
            'account_manager_id' => $otherManager->id,
        ]);

        $this->actingAs($manager);

        $this->post(route('manager.adjustments.store', $foreignAgent), [
            'wallet_type'  => 'cash',
            'points_delta' => 100,
            'reason'       => 'sneaky attempt',
        ])->assertNotFound();

        $this->assertDatabaseMissing('pending_adjustments', ['agent_id' => $foreignAgent->id]);
    }

    public function test_large_adjustment_from_manager_still_queues_not_applies(): void
    {
        $manager = User::factory()->accountManager()->create();
        $agent = Agent::factory()->withWallets()->create([
            'account_manager_id' => $manager->id,
        ]);

        $this->actingAs($manager);
        $agent->cashWallet->update(['available_points' => 500]);

        // Big delta would normally bypass dual-approval if admin did it directly
        $this->post(route('manager.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 5000,
            'reason'       => 'Large campaign bonus.',
        ])->assertRedirect();

        $this->assertDatabaseHas('pending_adjustments', [
            'agent_id'     => $agent->id,
            'points_delta' => 5000,
            'status'       => 'pending',
        ]);
        $this->assertSame(500, $agent->cashWallet->fresh()->available_points);
    }

    public function test_manager_can_cancel_own_suggestion(): void
    {
        $manager = User::factory()->accountManager()->create();
        $agent = Agent::factory()->withWallets()->create([
            'account_manager_id' => $manager->id,
        ]);

        $this->actingAs($manager);
        $this->post(route('manager.adjustments.store', $agent), [
            'wallet_type'  => 'cash',
            'points_delta' => 100,
            'reason'       => 'Bonus suggestion.',
        ]);

        $adj = PendingAdjustment::first();

        $this->post(route('manager.adjustments.cancel', $adj))->assertRedirect();
        $this->assertSame('cancelled', $adj->fresh()->status);
    }

    public function test_manager_cannot_cancel_other_managers_suggestion(): void
    {
        $manager      = User::factory()->accountManager()->create();
        $otherManager = User::factory()->accountManager()->create();
        $agent = Agent::factory()->withWallets()->create([
            'account_manager_id' => $otherManager->id,
        ]);

        $foreignAdj = PendingAdjustment::create([
            'agent_id'     => $agent->id,
            'wallet_type'  => 'cash',
            'points_delta' => 100,
            'reason'       => 'foreign suggestion',
            'requested_by' => $otherManager->id,
            'status'       => 'pending',
        ]);

        $this->actingAs($manager)
            ->post(route('manager.adjustments.cancel', $foreignAdj))
            ->assertNotFound();

        $this->assertSame('pending', $foreignAdj->fresh()->status);
    }

    public function test_adjustments_index_only_shows_managers_own_suggestions(): void
    {
        $manager      = User::factory()->accountManager()->create();
        $otherManager = User::factory()->accountManager()->create();
        $agent1 = Agent::factory()->withWallets()->create(['account_manager_id' => $manager->id]);
        $agent2 = Agent::factory()->withWallets()->create(['account_manager_id' => $otherManager->id]);

        PendingAdjustment::create([
            'agent_id' => $agent1->id, 'wallet_type' => 'cash', 'points_delta' => 50,
            'reason' => 'mine-unique-marker', 'requested_by' => $manager->id, 'status' => 'pending',
        ]);
        PendingAdjustment::create([
            'agent_id' => $agent2->id, 'wallet_type' => 'cash', 'points_delta' => 75,
            'reason' => 'theirs-unique-marker', 'requested_by' => $otherManager->id, 'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->get('/manager/adjustments')
            ->assertSee('mine-unique-marker')
            ->assertDontSee('theirs-unique-marker');
    }
}
