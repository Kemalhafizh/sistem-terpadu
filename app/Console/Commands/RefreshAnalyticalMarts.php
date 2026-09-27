<?php

namespace App\Console\Commands;

use App\Services\EdwOlapEngineService;
use Illuminate\Console\Command;

class RefreshAnalyticalMarts extends Command
{
    protected $signature = 'edw:refresh-marts {--date= : Tanggal agregasi (Format: YYYY-MM-DD)}';

    protected $description = 'Refresh pra-kalkulasi data mart EDW (OLAP) harian untuk dasbor analitik eksekutif';

    public function handle(EdwOlapEngineService $olapService): int
    {
        $date = $this->option('date') ?? now()->toDateString();

        $this->info("Memulai kalkulasi EDW Data Marts untuk tanggal [{$date}]...");

        $result = $olapService->refreshAllMarts($date);

        $this->info('Refresh EDW Data Marts Selesai:');
        $this->line('- Financial Data Mart: UPDATED');
        $this->line("- Student KPI Mart: {$result['student_kpis_count']} Record");
        $this->line("- Employee Performance Mart: {$result['employee_performances_count']} Record");

        return self::SUCCESS;
    }
}
