<?php

namespace App\Services\Crisis;

use App\Models\EmergencyBroadcast;
use Illuminate\Support\Str;

class EmergencyBroadcastService
{
    public function sendBroadcast(array $data): EmergencyBroadcast
    {
        $data['uuid'] = (string) Str::uuid();
        $data['sent_at'] = now();

        return EmergencyBroadcast::create($data);
    }

    public function getDeliveryStats(EmergencyBroadcast $broadcast): array
    {
        return [
            'total' => $broadcast->total_recipients,
            'delivered' => $broadcast->delivered_count,
            'read' => $broadcast->read_count,
        ];
    }
}
