<?php

namespace App\Console\Commands;

use App\Services\Academic\ClassSessionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * FASE 77.7.1 — daily per-child lesson attendance summary pushed to parents.
 * Scheduled at config('class_sessions.parent_digest_time'); a day's digest is
 * sent once unless --force.
 */
class SendParentAttendanceDigest extends Command
{
    protected $signature = 'class-sessions:parent-digest
        {--date= : Tanggal (Y-m-d), default hari ini}
        {--force : Kirim ulang walau rangkuman tanggal itu sudah terkirim}';

    protected $description = 'Kirim rangkuman kehadiran per-sesi hari ini ke orang tua.';

    public function handle(ClassSessionService $sessions): int
    {
        $date = $this->option('date') ? Carbon::parse((string) $this->option('date'))->toDateString() : now()->toDateString();

        if (! $this->option('force') && ! Cache::add("class-sessions:parent-digest:{$date}", true, now()->addDays(2))) {
            $this->warn("Rangkuman {$date} sudah pernah dikirim. Pakai --force untuk mengirim ulang.");

            return self::SUCCESS;
        }

        $sent = $sessions->sendParentDigest($date);
        $this->info("Rangkuman kehadiran {$date} terkirim ke {$sent} anak.");

        return self::SUCCESS;
    }
}
