<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Services\DigestService;
use Illuminate\Console\Command;
use Cron\CronExpression;

class SendScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sista:send-digests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate cron schedules and send out all due scheduled digests (Daily, Weekly, Monthly)';

    protected $digestService;

    public function __construct(DigestService $digestService)
    {
        parent::__construct();
        $this->digestService = $digestService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting scheduled reports dispatcher...');

        $reports = ScheduledReport::active()->get();
        $sentCount = 0;

        foreach ($reports as $report) {
            try {
                $cron = new CronExpression($report->cron_expression);
                
                if ($cron->isDue()) {
                    $this->info("Report '{$report->name}' is due. Sending...");
                    $this->digestService->sendDigest($report);
                    $this->info("Successfully sent report '{$report->name}'.");
                    $sentCount++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to process scheduled report ID {$report->id} ('{$report->name}'): " . $e->getMessage());
            }
        }

        $this->info("Dispatcher completed. Total reports sent: {$sentCount}.");
    }
}
