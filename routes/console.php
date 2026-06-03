<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- AUTOMATIC CLOUD BACKUP SCHEDULER ---
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('02:00');
Schedule::command('billing:send-reminders')->monthlyOn(1, '08:00');
Schedule::command('app:send-event-reminders')->dailyAt('07:00');
