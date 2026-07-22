<?php

namespace App\Listeners;

use App\Events\StudentGraduated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateAlumniRecord implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(StudentGraduated $event): void
    {
        Log::info("Generating Alumni Record for student ID {$event->student->id} ({$event->student->name}) - Batch {$event->graduationYear}");
    }
}
