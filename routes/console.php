<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automated Backup Schedule
Schedule::command('backup:database')->daily()->at('02:00')->name('daily-database-backup');
Schedule::command('backup:files')->daily()->at('02:30')->name('daily-files-backup');
Schedule::command('backup:cleanup --days=30 --keep-monthly')->daily()->at('03:00')->name('backup-cleanup');

// Account Expiration Notifications
Schedule::command('accounts:check-expiring')->daily()->at('09:00')->name('check-expiring-accounts');

// Account Expiration Deactivation
Schedule::command('accounts:deactivate-expired')->daily()->at('09:30')->name('deactivate-expired-accounts');
