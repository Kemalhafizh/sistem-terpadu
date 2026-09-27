<?php

namespace App\Services\DataEngineering;

use App\Models\EventStream;
use Illuminate\Support\Str;

class EventStreamingService
{
    public function publishEvent(string $aggType, string $aggId, string $eventType, array $payload, ?array $metadata = null)
    {
        return EventStream::create([
            'id' => Str::uuid()->toString(),
            'aggregate_type' => $aggType,
            'aggregate_id' => $aggId,
            'event_type' => $eventType,
            'payload' => $payload,
            'metadata' => $metadata,
            'version' => 1,
            'created_at' => now(),
        ]);
    }

    public function getRecentStreams($limit = 50)
    {
        return EventStream::orderBy('created_at', 'desc')->take($limit)->get();
    }
}
