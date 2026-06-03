<?php

namespace App\Exports;

use App\Services\AnalyticsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BKPointsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function collection()
    {
        $bk = $this->analyticsService->getPointStatistics();

        $data = collect();
        $data->push([
            'Kategori / Kelas' => 'RINGKASAN GLOBAL',
            'Poin Pelanggaran' => $bk['global']['penalty_points'],
            'Poin Penghargaan' => $bk['global']['reward_points'],
            'Jumlah Kasus Pelanggaran' => $bk['global']['penalty_count'],
            'Jumlah Kasus Penghargaan' => $bk['global']['reward_count'],
        ]);

        $data->push(['', '', '', '', '']);
        $data->push([
            'Kategori / Kelas' => 'RINCIAN PER KELAS',
            'Poin Pelanggaran' => '',
            'Poin Penghargaan' => '',
            'Jumlah Kasus Pelanggaran' => '',
            'Jumlah Kasus Penghargaan' => '',
        ]);

        foreach ($bk['by_classroom'] as $class) {
            $data->push([
                'Kategori / Kelas' => $class['classroom'],
                'Poin Pelanggaran' => $class['penalty_points'],
                'Poin Penghargaan' => $class['reward_points'],
                'Jumlah Kasus Pelanggaran' => '-',
                'Jumlah Kasus Penghargaan' => '-',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kategori / Kelas',
            'Poin Pelanggaran',
            'Poin Penghargaan',
            'Jumlah Kasus Pelanggaran',
            'Jumlah Kasus Penghargaan',
        ];
    }

    public function title(): string
    {
        return 'Statistik Poin BK';
    }
}
