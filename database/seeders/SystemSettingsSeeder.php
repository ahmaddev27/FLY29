<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Points calculation
            ['key' => 'calculation_method',        'value' => 'package_based',  'value_type' => 'string', 'category' => 'points',     'description' => 'package_based or amount_based',  'is_public' => true],
            ['key' => 'point_value_usd',           'value' => '2.0',            'value_type' => 'float',  'category' => 'points',     'description' => 'Dollar value per point',         'is_public' => true],

            // Redemption
            ['key' => 'min_redemption_points',     'value' => '800',            'value_type' => 'int',    'category' => 'redemption', 'description' => 'Min points for cash redemption', 'is_public' => true],
            ['key' => 'dual_approval_threshold',   'value' => '500',            'value_type' => 'int',    'category' => 'security',   'description' => 'Manual adjustment threshold requiring 2nd approval'],

            // Tier evaluation
            ['key' => 'tier_evaluation_mode',      'value' => 'calendar_month', 'value_type' => 'string', 'category' => 'tier',       'description' => 'calendar_month or rolling_30_days'],
            ['key' => 'tier_warning_days',         'value' => '7',              'value_type' => 'int',    'category' => 'tier',       'description' => 'Days before downgrade to send warning'],

            // Webhook
            ['key' => 'webhook_signature_verification', 'value' => 'true',      'value_type' => 'bool',   'category' => 'webhook',    'description' => 'Verify HMAC on incoming webhooks'],
            ['key' => 'webhook_rate_limit_per_min', 'value' => '100',           'value_type' => 'int',    'category' => 'webhook',    'description' => 'Max webhook requests per minute per API key'],

            // Defaults for new agents
            ['key' => 'default_tier_for_new_agent', 'value' => 'bronze',        'value_type' => 'string', 'category' => 'agents',     'description' => 'Initial tier for newly created agents'],

            // Auth
            ['key' => 'login_max_attempts',        'value' => '5',              'value_type' => 'int',    'category' => 'auth',       'description' => 'Failed login attempts before lockout'],
            ['key' => 'login_lockout_minutes',     'value' => '15',             'value_type' => 'int',    'category' => 'auth',       'description' => 'Lockout duration in minutes'],
            ['key' => 'session_lifetime_minutes',  'value' => '60',             'value_type' => 'int',    'category' => 'auth',       'description' => 'Session idle timeout'],

            // 2FA
            ['key' => 'two_factor_required_for_admin', 'value' => 'true',       'value_type' => 'bool',   'category' => 'auth',       'description' => 'Force 2FA for admin/super_admin roles'],

            // Mail / SMTP — override Laravel's mail config at runtime
            ['key' => 'mail_enabled',          'value' => 'false',              'value_type' => 'bool',     'category' => 'mail', 'description' => 'Master toggle — when off, all mail uses the .env config'],
            ['key' => 'mail_from_address',     'value' => 'no-reply@29fly.com', 'value_type' => 'string',   'category' => 'mail', 'description' => 'Default sender address'],
            ['key' => 'mail_from_name',        'value' => '29FLY Loyalty',      'value_type' => 'string',   'category' => 'mail', 'description' => 'Default sender display name'],
            ['key' => 'smtp_host',             'value' => '',                   'value_type' => 'string',   'category' => 'mail', 'description' => 'SMTP server hostname (e.g. smtp.mailgun.org)'],
            ['key' => 'smtp_port',             'value' => '587',                'value_type' => 'int',      'category' => 'mail', 'description' => 'SMTP port (587 for STARTTLS, 465 for SSL)'],
            ['key' => 'smtp_username',         'value' => '',                   'value_type' => 'string',   'category' => 'mail', 'description' => 'SMTP username'],
            ['key' => 'smtp_password',         'value' => '',                   'value_type' => 'password', 'category' => 'mail', 'description' => 'SMTP password (stored encrypted)'],
            ['key' => 'smtp_encryption',       'value' => 'tls',                'value_type' => 'string',   'category' => 'mail', 'description' => 'tls / ssl / none'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
