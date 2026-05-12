<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Applies SMTP credentials stored in system_settings to Laravel's mail
 * runtime config — overrides .env when the admin enables it.
 *
 * Boot-time call so every outgoing mail uses the configured settings.
 */
class MailConfigService
{
    public function __construct(private SettingsService $settings) {}

    public function applyFromSettings(): void
    {
        if (! $this->settings->get('mail_enabled', false)) {
            return; // fall back to .env / config/mail.php defaults
        }

        $host = (string) $this->settings->get('smtp_host', '');
        if ($host === '') {
            return; // not configured yet
        }

        $port       = (int) $this->settings->get('smtp_port', 587);
        $username   = (string) $this->settings->get('smtp_username', '');
        $password   = (string) $this->settings->get('smtp_password', '');
        $encryption = (string) $this->settings->get('smtp_encryption', 'tls');
        $fromAddr   = (string) $this->settings->get('mail_from_address', '');
        $fromName   = (string) $this->settings->get('mail_from_name', '');

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', $username ?: null);
        Config::set('mail.mailers.smtp.password', $password ?: null);
        Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);

        if ($fromAddr !== '') {
            Config::set('mail.from.address', $fromAddr);
        }
        if ($fromName !== '') {
            Config::set('mail.from.name', $fromName);
        }

        // Rebuild the Mail manager so it picks up the new config.
        app()->forgetInstance('mail.manager');
        Mail::clearResolvedInstance('mail.manager');
    }
}
