<?php

namespace App\Console\Commands;

use App\Services\Academic\StudentLeaveService;
use App\Services\ProfileChangeRequestService;
use Illuminate\Console\Command;

class ProcessStudentLeavesCommand extends Command
{
    protected $signature = 'student-leaves:process';

    protected $description = 'Sinkronkan presensi harian izin/sakit siswa yang aktif hari ini dan eskalasi pengajuan yang melewati tenggat.';

    public function handle(
        StudentLeaveService $leaveService,
        ProfileChangeRequestService $profileChangeService,
    ): int {
        $leaveStats = $leaveService->processDailyAndEscalate();
        $escalatedProfileRequests = $profileChangeService->escalateOverdue();

        $this->info(sprintf(
            'Izin siswa: %d sinkronisasi presensi, %d dieskalasi. Pengajuan perubahan data dieskalasi: %d.',
            $leaveStats['synced'],
            $leaveStats['escalated'],
            $escalatedProfileRequests,
        ));

        return self::SUCCESS;
    }
}
