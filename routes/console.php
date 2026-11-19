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

// tutup otomatis sesi kelas yang lupa diakhiri guru (jam selesai + 5 menit)
Schedule::command('class-sessions:auto-close')->everyMinute()->withoutOverlapping();
// pengingat guru 5 menit sebelum mulai/selesai + rangkuman harian orang tua
Schedule::command('class-sessions:remind')->everyMinute()->withoutOverlapping();
Schedule::command('class-sessions:parent-digest')->dailyAt(config('class_sessions.parent_digest_time', '17:00'))->onOneServer();

// WhatsApp Broadcast & Automation Triggers
Schedule::command('whatsapp:process-broadcasts')->everyMinute();
Schedule::command('whatsapp:process-broadcasts --run-triggers')->dailyAt('06:00');

// C3 & K4: Materialisasi izin/sakit harian siswa & eskalasi antrean melewati tenggat
Schedule::command('student-leaves:process')->hourly()->withoutOverlapping();
