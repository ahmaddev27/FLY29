<?php

namespace App\Console\Commands;

use App\Models\ApiLog;
use App\Models\AuditLog;
use Illuminate\Console\Command;

/**
 * Weekly housekeeping: prune log tables older than the retention window
 * to keep query performance reasonable. Audit logs are kept longer than
 * API logs because compliance often needs 7-year retention.
 */
class ArchiveOldLogsCommand extends Command
{
    protected $signature   = 'logs:archive
                             {--api-days=90  : Delete api_logs older than N days}
                             {--audit-days=2555 : Delete audit_logs older than N days (default 7 years)}';
    protected $description = 'Prune old api_logs and audit_logs rows past their retention window.';

    public function handle(): int
    {
        $apiDays   = (int) $this->option('api-days');
        $auditDays = (int) $this->option('audit-days');

        $apiCutoff   = now()->subDays($apiDays);
        $auditCutoff = now()->subDays($auditDays);

        $apiDeleted   = ApiLog::where('created_at', '<', $apiCutoff)->delete();
        $auditDeleted = AuditLog::where('created_at', '<', $auditCutoff)->delete();

        $this->info("Pruned {$apiDeleted} api_log(s) older than {$apiDays} days.");
        $this->info("Pruned {$auditDeleted} audit_log(s) older than {$auditDays} days.");

        return self::SUCCESS;
    }
}
