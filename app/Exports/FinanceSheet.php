<?php

namespace App\Exports;

use App\Services\AnalyticsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinanceSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function collection()
    {
        $finance = $this->analyticsService->getFinancialOverview();

        $data = collect();

        // Global Overview Row
        $data->push([
            'Kategori / Kelas' => 'RINGKASAN GLOBAL',
            'Total Tagihan (Rp)' => $finance['global']['total'],
            'Sudah Dibayar (Rp)' => $finance['global']['paid'],
            'Tunggakan (Rp)' => $finance['global']['unpaid'],
            'Persentase Lunas (%)' => $finance['global']['paid_percentage'].'%',
        ]);

        // Blank row
        $data->push([
            'Kategori / Kelas' => '',
            'Total Tagihan (Rp)' => '',
            'Sudah Dibayar (Rp)' => '',
            'Tunggakan (Rp)' => '',
            'Persentase Lunas (%)' => '',
        ]);

        $data->push([
            'Kategori / Kelas' => 'RINCIAN PER KELAS',
            'Total Tagihan (Rp)' => '',
            'Sudah Dibayar (Rp)' => '',
            'Tunggakan (Rp)' => '',
            'Persentase Lunas (%)' => '',
        ]);

        foreach ($finance['by_classroom'] as $class) {
            $data->push([
                'Kategori / Kelas' => $class['classroom'],
                'Total Tagihan (Rp)' => $class['total'],
                'Sudah Dibayar (Rp)' => $class['paid'],
                'Tunggakan (Rp)' => $class['unpaid'],
                'Persentase Lunas (%)' => $class['paid_percentage'].'%',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kategori / Kelas',
            'Total Tagihan (Rp)',
            'Sudah Dibayar (Rp)',
            'Tunggakan (Rp)',
            'Persentase Lunas (%)',
        ];
    }

    public function title(): string
    {
        return 'Keuangan Sekolah';
    }
}
