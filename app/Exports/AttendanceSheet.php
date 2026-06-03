<?php

namespace App\Exports;

use App\Services\AnalyticsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttendanceSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function collection()
    {
        $trend = $this->analyticsService->getAttendanceTrend();

        return $trend->map(function ($row) {
            return [
                'Bulan' => $row['month'],
                'Hadir' => $row['H'],
                'Sakit' => $row['S'],
                'Izin' => $row['I'],
                'Alpa' => $row['A'],
                'Total Presensi' => $row['total'],
                'Persentase Kehadiran (%)' => $row['percentage'].'%',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Hadir',
            'Sakit',
            'Izin',
            'Alpa',
            'Total Presensi',
            'Persentase Kehadiran (%)',
        ];
    }

    public function title(): string
    {
        return 'Tren Kehadiran';
    }
}
