<?php

namespace App\Console\Commands;

use App\Models\Billing;
use App\Notifications\BillingReminderNotification;
use Illuminate\Console\Command;

class SendBillingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly SPP and other outstanding billing reminders to parents via WhatsApp and Database notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to send billing reminders...');

        $billings = Billing::whereIn('status', ['unpaid', 'partial'])
            ->with(['student.parent', 'category'])
            ->get();

        $count = 0;
        foreach ($billings as $billing) {
            $student = $billing->student;
            if ($student && $student->parent) {
                $student->parent->notify(new BillingReminderNotification($billing));
                $count++;
            }
        }

        $this->info("Successfully sent {$count} billing reminders.");

        return Command::SUCCESS;
    }
}
