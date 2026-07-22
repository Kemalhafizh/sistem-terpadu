<?php

namespace App\Listeners;

use App\Events\StudentGraduated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyParentsViaWhatsApp implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'high';

    public function handle(StudentGraduated $event): void
    {
        Log::info("Sending High Priority WhatsApp Graduation Notification to parents of {$event->student->name}");
    }
}
