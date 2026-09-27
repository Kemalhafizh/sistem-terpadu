<?php

namespace App\Console\Commands;

use App\Services\Academic\ClassSessionService;
use Illuminate\Console\Command;

/**
 * FASE 117.5 — closes class sessions whose teacher forgot "Akhiri Kelas":
 * every active session more than the grace period past its scheduled end
 * becomes auto_closed, and students who never checked in stay alpha.
 * Scheduled every minute in routes/console.php.
 */
class AutoCloseClassSessions extends Command
{
    protected $signature = 'class-sessions:auto-close';

    protected $description = 'Tutup otomatis sesi kelas aktif yang sudah melewati jam selesai + toleransi.';

    public function handle(ClassSessionService $sessions): int
    {
        $closed = $sessions->autoCloseExpired();

        $this->info($closed === 0
            ? 'Tidak ada sesi kelas yang perlu ditutup otomatis.'
            : "{$closed} sesi kelas ditutup otomatis.");

        return self::SUCCESS;
    }
}
