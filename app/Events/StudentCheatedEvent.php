<?php

namespace App\Events;

use App\Models\ExamAttempt;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCheatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $attemptUuid;

    public string $studentName;

    public string $studentNis;

    public string $violationType;

    public int $violationCount;

    public string $timestamp;

    public int $examId;

    /**
     * Create a new event instance.
     */
    public function __construct(ExamAttempt $attempt, string $violationType)
    {
        $attempt->load(['student.user']);

        $this->attemptUuid = $attempt->uuid;
        $this->studentName = $attempt->student?->name ?? 'Siswa';
        $this->studentNis = $attempt->student?->nis ?? '-';
        $this->violationType = $violationType;
        $this->violationCount = $attempt->violation_count;
        $this->timestamp = now()->toTimeString();
        $this->examId = $attempt->exam_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('cbt.exam.'.$this->examId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'student-cheated';
    }
}
