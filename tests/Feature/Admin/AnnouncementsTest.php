<?php

namespace Tests\Feature\Admin;

use App\Mail\AnnouncementMail;
use App\Models\Agent;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AnnouncementsTest extends TestCase
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

    public function test_admin_can_view_announcements_list(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/announcements')
            ->assertOk()
            ->assertSee('الإعلانات');
    }

    public function test_admin_can_create_announcement_without_email(): void
    {
        $admin = $this->actingAsAdmin();

        $this->post('/admin/announcements', [
            'title'   => 'Welcome bonus this month',
            'body'    => 'All gold agents get 10% bonus this month.',
            'variant' => 'success',
        ])->assertRedirect(route('admin.announcements'));

        $a = Announcement::first();
        $this->assertNotNull($a);
        $this->assertSame('Welcome bonus this month', $a->title);
        $this->assertFalse($a->send_email);
        $this->assertSame($admin->id, $a->created_by);

        Mail::assertNothingSent();
    }

    public function test_admin_can_target_by_tier_and_send_email(): void
    {
        $this->actingAsAdmin();

        $goldAgent  = Agent::factory()->withWallets()->tier('gold')->create();
        $bronzeAgent = Agent::factory()->withWallets()->tier('bronze')->create();

        $this->post('/admin/announcements', [
            'title'       => 'Gold-only campaign',
            'body'        => 'Only for gold agents.',
            'variant'     => 'info',
            'tier_filter' => ['gold'],
            'send_email'  => '1',
        ])->assertRedirect();

        $a = Announcement::first();
        $this->assertSame(['gold'], $a->tier_filter);
        $this->assertTrue($a->send_email);

        // Only gold agent's email should be sent
        Mail::assertSent(AnnouncementMail::class, 1);
        Mail::assertSent(AnnouncementMail::class, function (AnnouncementMail $m) use ($goldAgent) {
            return $m->hasTo($goldAgent->user->email);
        });
        Mail::assertNotSent(AnnouncementMail::class, function (AnnouncementMail $m) use ($bronzeAgent) {
            return $m->hasTo($bronzeAgent->user->email);
        });
    }

    public function test_admin_can_toggle_announcement_active_state(): void
    {
        $this->actingAsAdmin();
        $a = Announcement::create([
            'title' => 'X', 'body' => 'Y', 'variant' => 'info',
            'created_by' => User::factory()->admin()->create()->id,
            'is_active' => true,
        ]);

        $this->patch(route('admin.announcements.toggle', $a))->assertRedirect();
        $this->assertFalse($a->fresh()->is_active);

        $this->patch(route('admin.announcements.toggle', $a))->assertRedirect();
        $this->assertTrue($a->fresh()->is_active);
    }

    public function test_admin_can_delete_announcement(): void
    {
        $this->actingAsAdmin();
        $a = Announcement::create([
            'title' => 'X', 'body' => 'Y', 'variant' => 'info',
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->delete(route('admin.announcements.destroy', $a))->assertRedirect();
        $this->assertDatabaseMissing('announcements', ['id' => $a->id]);
    }

    public function test_validation_requires_title_and_body(): void
    {
        $this->actingAsAdmin();

        $this->post('/admin/announcements', ['variant' => 'info'])
            ->assertSessionHasErrors(['title', 'body']);
    }

    /* -------------------------------------------------------------- */
    /* Agent side                                                     */
    /* -------------------------------------------------------------- */

    public function test_agent_sees_active_announcements_targeted_to_them(): void
    {
        $agent = Agent::factory()->withWallets()->tier('gold')->create();
        $this->actingAs($agent->user);

        Announcement::create([
            'title' => 'Gold-targeted banner', 'body' => 'Body',
            'variant' => 'info', 'tier_filter' => ['gold'],
            'is_active' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ]);
        Announcement::create([
            'title' => 'Bronze-only banner', 'body' => 'Body',
            'variant' => 'info', 'tier_filter' => ['bronze'],
            'is_active' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ]);
        Announcement::create([
            'title' => 'Disabled banner', 'body' => 'Body',
            'variant' => 'info', 'is_active' => false,
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->get('/agent/dashboard')
            ->assertSee('Gold-targeted banner')
            ->assertDontSee('Bronze-only banner')
            ->assertDontSee('Disabled banner');
    }

    public function test_dismissing_an_announcement_hides_it_for_that_agent(): void
    {
        $agent = Agent::factory()->withWallets()->tier('gold')->create();
        $this->actingAs($agent->user);

        $a = Announcement::create([
            'title' => 'One-time only', 'body' => 'Body',
            'variant' => 'info', 'is_active' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        // First load — visible
        $this->get('/agent/dashboard')->assertSee('One-time only');

        // Dismiss
        $this->post(route('agent.announcements.dismiss', $a))->assertRedirect();

        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $a->id,
            'agent_id'        => $agent->id,
        ]);

        // Second load — no longer visible
        $this->get('/agent/dashboard')->assertDontSee('One-time only');
    }

    public function test_expired_announcements_are_not_shown(): void
    {
        $agent = Agent::factory()->withWallets()->create();
        $this->actingAs($agent->user);

        Announcement::create([
            'title' => 'Expired banner XYZ', 'body' => 'Body',
            'variant' => 'info', 'is_active' => true,
            'expires_at' => now()->subDay(),
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->get('/agent/dashboard')->assertDontSee('Expired banner XYZ');
    }
}
