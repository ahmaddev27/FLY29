<?php

namespace Tests\Feature\Admin;

use App\Models\Agent;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountManagersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
        Mail::fake();
    }

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        return $admin;
    }

    public function test_admin_sees_account_managers_list(): void
    {
        $this->actingAsAdmin();
        User::factory()->accountManager()->count(2)->create();

        $this->get('/admin/account-managers')
            ->assertOk()
            ->assertSee('مدراء الحسابات');
    }

    public function test_admin_can_create_account_manager(): void
    {
        $this->actingAsAdmin();

        $this->post('/admin/account-managers', [
            'full_name' => 'Manager One',
            'email'     => 'mgr1@example.com',
            'phone'     => '+966500000099',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'mgr1@example.com',
            'role'  => 'account_manager',
            'status' => 'active',
        ]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $this->actingAsAdmin();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/admin/account-managers', [
            'full_name' => 'Manager',
            'email'     => 'taken@example.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_show_page_shows_assigned_agents(): void
    {
        $this->actingAsAdmin();
        $manager = User::factory()->accountManager()->create(['full_name' => 'Sarah PM']);

        $assigned = Agent::factory()->withWallets()->create([
            'account_manager_id' => $manager->id,
            'business_name'      => 'Assigned Co',
        ]);
        Agent::factory()->withWallets()->create([
            'account_manager_id' => null,
            'business_name'      => 'Unassigned Co',
        ]);

        $this->get("/admin/account-managers/{$manager->id}")
            ->assertOk()
            ->assertSee('Sarah PM')
            ->assertSee('Assigned Co')
            ->assertSee('Unassigned Co'); // appears in the "assign new" panel
    }

    public function test_non_account_manager_user_returns_404(): void
    {
        $this->actingAsAdmin();
        $agent = User::factory()->create(['role' => 'agent']);

        $this->get("/admin/account-managers/{$agent->id}")->assertNotFound();
    }

    public function test_admin_can_assign_agents_to_manager(): void
    {
        $this->actingAsAdmin();
        $manager = User::factory()->accountManager()->create();
        $a1 = Agent::factory()->withWallets()->create(['account_manager_id' => null]);
        $a2 = Agent::factory()->withWallets()->create(['account_manager_id' => null]);

        $this->post("/admin/account-managers/{$manager->id}/assign", [
            'agent_ids' => [$a1->id, $a2->id],
        ])->assertRedirect();

        $this->assertSame($manager->id, $a1->fresh()->account_manager_id);
        $this->assertSame($manager->id, $a2->fresh()->account_manager_id);
    }

    public function test_admin_can_unassign_an_agent(): void
    {
        $this->actingAsAdmin();
        $manager = User::factory()->accountManager()->create();
        $agent = Agent::factory()->withWallets()->create(['account_manager_id' => $manager->id]);

        $this->delete("/admin/account-managers/{$manager->id}/agents/{$agent->id}")
            ->assertRedirect();

        $this->assertNull($agent->fresh()->account_manager_id);
    }

    public function test_unassigning_an_agent_from_wrong_manager_returns_404(): void
    {
        $this->actingAsAdmin();
        $manager1 = User::factory()->accountManager()->create();
        $manager2 = User::factory()->accountManager()->create();
        $agent = Agent::factory()->withWallets()->create(['account_manager_id' => $manager1->id]);

        $this->delete("/admin/account-managers/{$manager2->id}/agents/{$agent->id}")
            ->assertNotFound();
    }

    public function test_admin_can_suspend_and_unsuspend_manager(): void
    {
        $this->actingAsAdmin();
        $manager = User::factory()->accountManager()->create();

        $this->patch("/admin/account-managers/{$manager->id}/suspend", ['reason' => 'Performance review pending.'])
            ->assertRedirect();
        $this->assertSame('suspended', $manager->fresh()->status);

        $this->patch("/admin/account-managers/{$manager->id}/unsuspend")
            ->assertRedirect();
        $this->assertSame('active', $manager->fresh()->status);
    }

    public function test_destroying_manager_releases_assigned_agents(): void
    {
        $this->actingAsAdmin();
        $manager = User::factory()->accountManager()->create();
        $a1 = Agent::factory()->withWallets()->create(['account_manager_id' => $manager->id]);
        $a2 = Agent::factory()->withWallets()->create(['account_manager_id' => $manager->id]);

        $this->delete("/admin/account-managers/{$manager->id}")->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $manager->id]);
        $this->assertNull($a1->fresh()->account_manager_id);
        $this->assertNull($a2->fresh()->account_manager_id);
    }
}
