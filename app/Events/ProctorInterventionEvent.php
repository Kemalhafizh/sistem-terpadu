<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProctorInterventionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $examId;
    public ?int $studentId;
    public string $actionType; // WARNING_MODAL, FORCE_SUBMIT, EXTEND_TIME, UNLOCK_EXAM
    public ?string $message;
    public ?int $extraMinutes;
    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $examId,
        ?int $studentId,
        string $actionType,
        ?string $message = null,
        ?int $extraMinutes = null
    ) {
        $this->examId = $examId;
        $this->studentId = $studentId;
        $this->actionType = $actionType;
        $this->message = $message;
        $this->extraMinutes = $extraMinutes;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('cbt.exam.' . $this->examId),
        ];

        if ($this->studentId) {
            $channels[] = new PrivateChannel('cbt.exam.' . $this->examId . '.student.' . $this->studentId);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'proctor-intervention';
    }
}
