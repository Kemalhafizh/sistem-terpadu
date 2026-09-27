<?php

namespace App\Console\Commands;

use App\Services\Academic\ClassSessionService;
use Illuminate\Console\Command;

/**
 * FASE 77.7.1 — push the teacher 5 minutes before a lesson starts and before
 * an active lesson ends. Scheduled every minute; each reminder is sent once.
 */
class RemindClassSessions extends Command
{
    protected $signature = 'class-sessions:remind';

    protected $description = 'Kirim pengingat ke guru 5 menit sebelum sesi kelas dimulai/berakhir.';

    public function handle(ClassSessionService $sessions): int
    {
        $sent = $sessions->sendDueReminders();

        $this->info("Pengingat terkirim: {$sent['start']} akan dimulai, {$sent['end']} akan berakhir.");

        return self::SUCCESS;
    }
}
