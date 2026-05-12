<?php

namespace Tests\Feature\Admin;

use App\Mail\AgentWelcomeMail;
use App\Models\Agent;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AgentsManagementTest extends TestCase
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

    public function test_non_admin_cannot_access_agents_index(): void
    {
        $user = User::factory()->create(['role' => 'agent']);
        $this->actingAs($user);

        $this->get('/admin/agents')->assertForbidden();
    }

    public function test_admin_sees_agents_list(): void
    {
        $this->actingAsAdmin();
        Agent::factory()->count(3)->withWallets()->create();

        $this->get('/admin/agents')
            ->assertOk()
            ->assertSee('الوكلاء');
    }

    public function test_search_filters_by_business_name(): void
    {
        $this->actingAsAdmin();
        Agent::factory()->withWallets()->create(['business_name' => 'Aladin Travel']);
        Agent::factory()->withWallets()->create(['business_name' => 'Sahara Tours']);

        $this->get('/admin/agents?q=Aladin')
            ->assertOk()
            ->assertSee('Aladin Travel')
            ->assertDontSee('Sahara Tours');
    }

    public function test_filter_by_tier(): void
    {
        $this->actingAsAdmin();
        Agent::factory()->withWallets()->tier('gold')->create(['business_name' => 'Gold Co']);
        Agent::factory()->withWallets()->tier('bronze')->create(['business_name' => 'Bronze Co']);

        $this->get('/admin/agents?tier=gold')
            ->assertOk()
            ->assertSee('Gold Co')
            ->assertDontSee('Bronze Co');
    }

    public function test_admin_can_view_agent_profile(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create(['business_name' => 'Test Co']);

        $this->get("/admin/agents/{$agent->id}")
            ->assertOk()
            ->assertSee('Test Co')
            ->assertSee($agent->external_agent_id);
    }

    public function test_admin_can_create_agent(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        $payload = [
            'full_name'         => 'Aladin Ahmed',
            'email'             => 'aladin@example.com',
            'phone'             => '+966500000001',
            'external_agent_id' => 'AGT-001',
            'business_name'     => 'Aladin Travel',
            'license_number'    => 'LIC-99',
            'country'           => 'SA',
            'city'              => 'Riyadh',
            'current_tier'      => 'bronze',
        ];

        $this->post('/admin/agents', $payload)->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'aladin@example.com', 'role' => 'agent']);
        $this->assertDatabaseHas('agents', [
            'external_agent_id' => 'AGT-001',
            'business_name'     => 'Aladin Travel',
        ]);

        $agent = Agent::where('external_agent_id', 'AGT-001')->first();
        $this->assertNotNull($agent->cashWallet);
        $this->assertNotNull($agent->packageWallet);
        $this->assertSame(0, $agent->cashWallet->available_points);

        Mail::assertSent(AgentWelcomeMail::class, function (AgentWelcomeMail $mail) use ($agent) {
            return $mail->hasTo($agent->user->email)
                && str_contains($mail->passwordSetupUrl, 'reset-password/')
                && str_contains($mail->passwordSetupUrl, 'email=');
        });

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'agent_created',
            'entity_type' => Agent::class,
            'entity_id'   => (string) $agent->id,
        ]);
    }

    public function test_duplicate_external_id_is_rejected(): void
    {
        $this->actingAsAdmin();
        Agent::factory()->withWallets()->create(['external_agent_id' => 'AGT-DUP']);

        $this->post('/admin/agents', [
            'full_name'         => 'X',
            'email'             => 'x@example.com',
            'external_agent_id' => 'AGT-DUP',
            'business_name'     => 'X Co',
            'license_number'    => 'LIC-X',
            'country'           => 'SA',
        ])->assertSessionHasErrors('external_agent_id');
    }

    public function test_admin_can_suspend_agent_with_reason(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create();

        $this->patch("/admin/agents/{$agent->id}/suspend", [
            'reason' => 'Violation of terms',
        ])->assertRedirect();

        $this->assertSame('suspended', $agent->user->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'agent_suspended',
            'entity_id' => (string) $agent->id,
        ]);
    }

    public function test_suspend_requires_reason(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create();

        $this->patch("/admin/agents/{$agent->id}/suspend", [])
            ->assertSessionHasErrors('reason');
    }

    public function test_admin_can_unsuspend_agent(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->suspended()->create();

        $this->patch("/admin/agents/{$agent->id}/unsuspend")->assertRedirect();

        $this->assertSame('active', $agent->user->fresh()->status);
    }

    public function test_admin_can_soft_delete_agent(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create();
        $userId = $agent->user_id;

        $this->delete("/admin/agents/{$agent->id}")->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $userId]);
        $this->assertSame('deleted', User::withTrashed()->find($userId)->status);
    }

    public function test_admin_can_save_internal_notes(): void
    {
        $this->actingAsAdmin();
        $agent = Agent::factory()->withWallets()->create();

        $this->patch("/admin/agents/{$agent->id}/notes", [
            'internal_notes' => 'High-value agent, prioritize support.',
        ])->assertRedirect();

        $this->assertSame(
            'High-value agent, prioritize support.',
            $agent->fresh()->internal_notes,
        );
    }

    public function test_excel_import_creates_valid_rows_and_skips_invalid(): void
    {
        Mail::fake();
        $this->actingAsAdmin();

        // Existing agent — used to test duplicate detection
        Agent::factory()->withWallets()->create(['external_agent_id' => 'AGT-DUP']);

        $csv = "full_name,email,phone,external_agent_id,business_name,license_number,country,city,current_tier\n"
             . "Aladin Travel,aladin@example.com,+966500000001,AGT-NEW-1,Aladin Co,LIC-1,SA,Riyadh,bronze\n"
             . "Sahara Tours,sahara@example.com,+966500000002,AGT-NEW-2,Sahara Co,LIC-2,SA,Jeddah,silver\n"
             . ",bademail@example.com,,AGT-NEW-3,Bad Co,LIC-3,SA,,gold\n"  // missing full_name → fail
             . "Dup Co,dup@example.com,,AGT-DUP,Dup Co,LIC-X,SA,,bronze\n"; // duplicate external_id → fail

        $file = UploadedFile::fake()->createWithContent('agents.csv', $csv);

        $response = $this->post('/admin/agents/import', ['file' => $file]);

        $response->assertRedirect(route('admin.agents'));
        $response->assertSessionHas('import_errors');

        $this->assertDatabaseHas('agents', ['external_agent_id' => 'AGT-NEW-1']);
        $this->assertDatabaseHas('agents', ['external_agent_id' => 'AGT-NEW-2']);
        $this->assertDatabaseMissing('agents', ['external_agent_id' => 'AGT-NEW-3']);

        // 2 successful → 2 welcome mails
        Mail::assertSent(AgentWelcomeMail::class, 2);

        $errors = session('import_errors');
        $this->assertCount(2, $errors);
    }

    public function test_import_rejects_non_excel_files(): void
    {
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->create('virus.exe', 100);

        $this->post('/admin/agents/import', ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_import_template_download_works(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/agents/import/template')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
