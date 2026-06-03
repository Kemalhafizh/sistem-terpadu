<?php

namespace App\Exports;

use App\Services\AnalyticsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TeacherPerformanceSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function collection()
    {
        $teachers = $this->analyticsService->getTeacherPerformanceMetrics();

        return collect($teachers['performance'])->map(function ($row) {
            return [
                'Nama Guru' => $row->name,
                'Total Jurnal Ditulis' => $row->total_journals,
                'Jurnal Sudah Direview' => $row->reviewed_journals,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Guru',
            'Total Jurnal Ditulis',
            'Jurnal Sudah Direview',
        ];
    }

    public function title(): string
    {
        return 'Kinerja Guru (Jurnal)';
    }
}
