<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $emergencyData
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('campus.emergency'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'emergency.alert';
    }
}
