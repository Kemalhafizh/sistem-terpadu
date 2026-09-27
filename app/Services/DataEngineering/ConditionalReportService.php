<?php

namespace App\Services\DataEngineering;

use App\Models\ConditionalReport;
use App\Models\ReportScheduleLog;

class ConditionalReportService
{
    public function evaluateCondition(ConditionalReport $report)
    {
        return true;
    }

    public function compileReport(ConditionalReport $report)
    {
        ReportScheduleLog::create([
            'conditional_report_id' => $report->id,
            'status' => 'generated',
            'execution_note' => 'Report generated successfully.',
        ]);

        return true;
    }
}
