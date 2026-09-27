<?php

namespace App\Console\Commands;

use App\Services\SmartAlertService;
use Illuminate\Console\Command;

class GenerateSmartAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-smart-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate SISTA Smart Alerts (Predictive Analytics & Deteksi Dini)';

    /**
     * Execute the console command.
     */
    public function handle(SmartAlertService $service)
    {
        $this->info('Starting Smart Alerts generation...');

        $results = $service->generateAll();

        $this->info('Completed. Total generated/updated active alerts: '.$results['total']);
        foreach ($results['details'] as $type => $count) {
            $this->line(" - {$type}: {$count}");
        }

        return self::SUCCESS;
    }
}
