<?php

namespace App\Listeners;

use App\Events\ExamCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CalculateCbtScore implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(ExamCompleted $event): void
    {
        Log::info("Calculating CBT Score asynchronously for exam #{$event->examId} student {$event->student->name}");
    }
}
