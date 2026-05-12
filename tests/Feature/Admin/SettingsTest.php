<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsTest extends TestCase
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

    public function test_non_admin_cannot_access_settings(): void
    {
        $user = User::factory()->create(['role' => 'agent']);
        $this->actingAs($user);

        $this->get('/admin/settings')->assertForbidden();
    }

    public function test_admin_can_view_settings_page(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/settings')
            ->assertOk()
            ->assertSee('إعدادات النظام')
            ->assertSee('point_value_usd');
    }

    public function test_admin_can_update_int_setting(): void
    {
        $this->actingAsAdmin();

        $this->patch('/admin/settings', [
            'settings' => ['min_redemption_points' => 1200],
        ])->assertRedirect();

        $this->assertSame('1200', SystemSetting::find('min_redemption_points')->value);
    }

    public function test_admin_can_update_float_setting(): void
    {
        $this->actingAsAdmin();

        $this->patch('/admin/settings', [
            'settings' => ['point_value_usd' => 2.5],
        ])->assertRedirect();

        $this->assertSame(2.5, SystemSetting::find('point_value_usd')->typedValue());
    }

    public function test_admin_can_toggle_bool_setting(): void
    {
        $this->actingAsAdmin();

        // First, turn it off
        $this->patch('/admin/settings', [
            'settings' => ['webhook_signature_verification' => 0],
        ])->assertRedirect();

        $this->assertFalse(SystemSetting::find('webhook_signature_verification')->typedValue());

        // Then, turn it back on
        $this->patch('/admin/settings', [
            'settings' => ['webhook_signature_verification' => 1],
        ])->assertRedirect();

        $this->assertTrue(SystemSetting::find('webhook_signature_verification')->typedValue());
    }

    public function test_admin_can_update_enum_string_setting(): void
    {
        $this->actingAsAdmin();

        $this->patch('/admin/settings', [
            'settings' => ['calculation_method' => 'amount_based'],
        ])->assertRedirect();

        $this->assertSame('amount_based', SystemSetting::find('calculation_method')->value);
    }

    public function test_int_setting_rejects_non_numeric_value(): void
    {
        $this->actingAsAdmin();

        $this->patch('/admin/settings', [
            'settings' => ['min_redemption_points' => 'abc'],
        ])->assertSessionHasErrors('settings.min_redemption_points');
    }

    public function test_update_invalidates_settings_cache(): void
    {
        $admin = $this->actingAsAdmin();

        $service = app(\App\Services\SettingsService::class);

        // Warm the cache
        $this->assertSame(800, $service->get('min_redemption_points'));

        $this->patch('/admin/settings', [
            'settings' => ['min_redemption_points' => 1500],
        ])->assertRedirect();

        $this->assertSame(1500, $service->get('min_redemption_points'));
    }

    public function test_change_is_logged_to_audit(): void
    {
        $admin = $this->actingAsAdmin();

        $this->patch('/admin/settings', [
            'settings' => ['min_redemption_points' => 1100],
        ])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id'     => $admin->id,
            'action'      => 'settings_updated',
            'entity_type' => SystemSetting::class,
        ]);
    }

    public function test_unknown_setting_keys_are_ignored(): void
    {
        $this->actingAsAdmin();
        $beforeCount = SystemSetting::count();

        $this->patch('/admin/settings', [
            'settings' => ['unknown_made_up_key' => 'whatever'],
        ])->assertRedirect();

        $this->assertSame($beforeCount, SystemSetting::count());
    }
}
