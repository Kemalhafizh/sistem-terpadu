<?php

namespace App\Exports;

use App\Services\AnalyticsService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnalyticsExport implements WithMultipleSheets
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function sheets(): array
    {
        return [
            new AttendanceSheet($this->analyticsService),
            new FinanceSheet($this->analyticsService),
            new GradesSheet($this->analyticsService),
            new BKPointsSheet($this->analyticsService),
            new TeacherPerformanceSheet($this->analyticsService),
        ];
    }
}
