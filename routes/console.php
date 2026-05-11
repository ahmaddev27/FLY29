<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| 29FLY Loyalty - Scheduled Tasks
|--------------------------------------------------------------------------
| To enable: register cron on the server with the following entry:
|   * * * * * cd /path/to/Fly && php artisan schedule:run >> /dev/null 2>&1
|
| All times are in Asia/Riyadh (configured in .env / config/app.php).
*/

// Daily 02:00 - Evaluate tiers (downgrade + 7-day warning)
// Schedule::command('tiers:evaluate')->dailyAt('02:00')->timezone('Asia/Riyadh');

// Daily 03:00 - Reconcile transactions with Main Site
// Schedule::command('transactions:reconcile')->dailyAt('03:00')->timezone('Asia/Riyadh');

// Daily 04:00 - Cleanup expired password tokens
// Schedule::command('tokens:cleanup')->dailyAt('04:00')->timezone('Asia/Riyadh');

// Weekly Friday 05:00 - Archive logs older than 90 days
// Schedule::command('logs:archive')->weeklyOn(5, '05:00')->timezone('Asia/Riyadh');
