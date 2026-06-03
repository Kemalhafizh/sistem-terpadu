<?php

namespace App\Exports;

use App\Services\AnalyticsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GradesSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function collection()
    {
        $grades = $this->analyticsService->getGradeDistribution();

        $data = collect();
        $data->push(['KATEGORI: TOP 10 RATA-RATA NILAI MATA PELAJARAN', '']);
        foreach ($grades['top_subjects'] as $sub) {
            $data->push([
                'Mata Pelajaran' => $sub->name,
                'Rata-rata Nilai' => $sub->average,
            ]);
        }

        $data->push(['', '']);
        $data->push(['KATEGORI: DISTRIBUSI RENTANG NILAI', '']);
        foreach ($grades['distribution'] as $range => $count) {
            $data->push([
                'Rentang Nilai' => 'Rentang '.$range,
                'Jumlah Siswa' => $count,
            ]);
        }

        $data->push(['', '']);
        $data->push(['KATEGORI: RATA-RATA NILAI PER KELAS', '']);
        foreach ($grades['class_averages'] as $class) {
            $data->push([
                'Kelas' => $class->name,
                'Rata-rata Nilai' => $class->average,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Parameter / Rincian',
            'Nilai / Jumlah',
        ];
    }

    public function title(): string
    {
        return 'Akademik & Nilai';
    }
}
