<?php

namespace App\Console\Commands;

use App\Services\Academic\StudentLeaveService;
use App\Services\ProfileChangeRequestService;
use App\Services\WorkflowService;
use Illuminate\Console\Command;

class ProcessStudentLeavesCommand extends Command
{
    protected $signature = 'student-leaves:process';

    protected $description = 'Sinkronkan presensi harian izin/sakit siswa serta jalankan pengingat dan eskalasi SLA antrean persetujuan.';

    public function handle(
        StudentLeaveService $leaveService,
        ProfileChangeRequestService $profileChangeService,
        WorkflowService $workflowService,
    ): int {
        $leaveStats = $leaveService->processDailyAndEscalate();
        $escalatedProfileRequests = $profileChangeService->escalateOverdue();
        $workflowSlaStats = $workflowService->processSlaAndEscalate();

        $this->info(sprintf(
            'Izin siswa: %d sinkronisasi presensi, %d dieskalasi. Perubahan data dieskalasi: %d. Workflow SLA: %d pengingat, %d dieskalasi.',
            $leaveStats['synced'],
            $leaveStats['escalated'],
            $escalatedProfileRequests,
            $workflowSlaStats['reminded'],
            $workflowSlaStats['escalated'],
        ));

        return self::SUCCESS;
    }
}
