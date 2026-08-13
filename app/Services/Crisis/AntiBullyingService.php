<?php

namespace App\Services\Crisis;

use App\Models\BullyingReport;
use App\Models\BullyingInvestigationLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AntiBullyingService
{
    public function submitReport(array $data): BullyingReport
    {
        $data['ticket_number'] = (string) Str::uuid();
        $data['status'] = 'reported';
        return BullyingReport::create($data);
    }

    public function assignInvestigator(BullyingReport $report, int $userId): void
    {
        $report->update([
            'assigned_to' => $userId,
            'status' => 'under_review'
        ]);
    }

    public function logInvestigation(BullyingReport $report, array $data): BullyingInvestigationLog
    {
        $data['bullying_report_id'] = $report->id;
        return BullyingInvestigationLog::create($data);
    }

    public function getStatsByType(): array
    {
        return BullyingReport::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }
}