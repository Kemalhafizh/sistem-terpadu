<?php

namespace App\Services\Compliance;

use App\Models\ForensicEvent;

class AuditConsoleService
{
    public function logEvent(string $type, string $ip, ?int $userId = null, ?array $payload = null, string $severity = 'info'): ForensicEvent
    {
        return ForensicEvent::create([
            'user_id' => $userId,
            'event_type' => $type,
            'ip_address' => $ip,
            'payload' => $payload,
            'severity' => $severity,
            'occurred_at' => now(),
        ]);
    }
}
