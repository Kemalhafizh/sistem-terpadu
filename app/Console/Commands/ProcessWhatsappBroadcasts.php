<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsappBroadcastJob;
use App\Models\WhatsappBroadcast;
use App\Services\WhatsappAutomationService;
use Illuminate\Console\Command;

class ProcessWhatsappBroadcasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:process-broadcasts {--run-triggers : Also run daily scheduled triggers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued and scheduled WhatsApp broadcasts and run daily automation checks';

    /**
     * Execute the console command.
     */
    public function handle(WhatsappAutomationService $automationService)
    {
        $this->info('Checking for scheduled broadcasts...');

        // 1. Process Scheduled Broadcasts
        $broadcasts = WhatsappBroadcast::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        $this->info('Found ' . $broadcasts->count() . ' broadcasts to dispatch.');

        foreach ($broadcasts as $broadcast) {
            $this->info("Dispatching broadcast: {$broadcast->title}");
            SendWhatsappBroadcastJob::dispatch($broadcast);
        }

        // 2. Process Scheduled Triggers (if flag is passed or run daily)
        if ($this->option('run-triggers')) {
            $this->info('Running daily scheduled automation triggers...');
            $automationService->runScheduledTriggers();
            $this->info('Daily scheduled triggers completed.');
        }

        return Command::SUCCESS;
    }
}
