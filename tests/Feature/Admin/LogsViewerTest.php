<?php

namespace Tests\Feature\Admin;

use App\Models\ApiLog;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\AgentLevelsSeeder;
use Database\Seeders\SystemSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogsViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AgentLevelsSeeder::class, SystemSettingsSeeder::class]);
    }

    /* ------------------------------------------------------------------ */
    /* Audit Log                                                          */
    /* ------------------------------------------------------------------ */

    public function test_super_admin_can_view_audit_log(): void
    {
        $sa = User::factory()->superAdmin()->create();

        AuditLog::create([
            'user_id'     => $sa->id,
            'action'      => 'settings_updated',
            'entity_type' => 'App\Models\SystemSetting',
            'entity_id'   => null,
            'new_values'  => ['min_redemption_points' => 800],
            'ip_address'  => '127.0.0.1',
        ]);

        $this->actingAs($sa)
            ->get('/admin/audit')
            ->assertOk()
            ->assertSee('سجل التدقيق')
            ->assertSee('settings_updated');
    }

    public function test_regular_admin_cannot_view_audit_log(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/audit')
            ->assertForbidden();
    }

    public function test_audit_log_search_filters_by_action(): void
    {
        $sa = User::factory()->superAdmin()->create();

        AuditLog::create([
            'user_id'     => $sa->id,
            'action'      => 'agent_created',
            'entity_type' => 'App\Models\Agent',
            'entity_id'   => 'ID-CREATED-X',
        ]);
        AuditLog::create([
            'user_id'     => $sa->id,
            'action'      => 'agent_suspended',
            'entity_type' => 'App\Models\Agent',
            'entity_id'   => 'ID-SUSPENDED-X',
        ]);

        // The filter dropdown lists every distinct action so 'agent_suspended'
        // is on the page either way — assert on the unique entity_id instead.
        $this->actingAs($sa)
            ->get('/admin/audit?action=agent_created')
            ->assertSee('ID-CREATED-X')
            ->assertDontSee('ID-SUSPENDED-X');
    }

    public function test_audit_log_filters_by_date_range(): void
    {
        $sa = User::factory()->superAdmin()->create();

        $old = AuditLog::create([
            'user_id'     => $sa->id,
            'action'      => 'date_test_action',
            'entity_type' => 'X',
            'entity_id'   => 'OLD-ROW-ID',
        ]);
        // created_at isn't in $fillable, so set it via the query builder.
        \DB::table('audit_logs')->where('id', $old->id)->update(['created_at' => now()->subDays(10)]);

        AuditLog::create([
            'user_id'     => $sa->id,
            'action'      => 'date_test_action',
            'entity_type' => 'X',
            'entity_id'   => 'NEW-ROW-ID',
        ]);

        $from = now()->subDays(2)->toDateString();

        $this->actingAs($sa)
            ->get("/admin/audit?from={$from}")
            ->assertSee('NEW-ROW-ID')
            ->assertDontSee('OLD-ROW-ID');
    }

    /* ------------------------------------------------------------------ */
    /* API Log                                                            */
    /* ------------------------------------------------------------------ */

    public function test_admin_can_view_api_logs_index(): void
    {
        $admin = User::factory()->admin()->create();

        ApiLog::create([
            'method'        => 'POST',
            'endpoint'      => '/api/v1/transactions/ingest',
            'response_code' => 200,
            'status'        => 'success',
            'ip_address'    => '1.2.3.4',
            'reference_id'  => 'TXN-001',
        ]);

        $this->actingAs($admin)
            ->get('/admin/api-logs')
            ->assertOk()
            ->assertSee('سجل الـ API')
            ->assertSee('TXN-001');
    }

    public function test_api_log_search_filters_by_reference_id(): void
    {
        $admin = User::factory()->admin()->create();

        ApiLog::create([
            'method'        => 'POST',
            'endpoint'      => '/api/v1/x',
            'response_code' => 200,
            'status'        => 'success',
            'reference_id'  => 'AAA-111',
        ]);
        ApiLog::create([
            'method'        => 'POST',
            'endpoint'      => '/api/v1/x',
            'response_code' => 200,
            'status'        => 'success',
            'reference_id'  => 'BBB-222',
        ]);

        $this->actingAs($admin)
            ->get('/admin/api-logs?q=AAA')
            ->assertSee('AAA-111')
            ->assertDontSee('BBB-222');
    }

    public function test_api_log_show_displays_full_detail(): void
    {
        $admin = User::factory()->admin()->create();

        $log = ApiLog::create([
            'method'         => 'POST',
            'endpoint'       => '/api/v1/transactions/ingest',
            'request_body'   => ['agent_id' => 'AGT-1'],
            'response_code'  => 200,
            'response_body'  => ['status' => 'accepted'],
            'status'         => 'success',
            'reference_id'   => 'TXN-DETAIL',
        ]);

        $this->actingAs($admin)
            ->get("/admin/api-logs/{$log->id}")
            ->assertOk()
            ->assertSee('TXN-DETAIL')
            ->assertSee('AGT-1');
    }
}
