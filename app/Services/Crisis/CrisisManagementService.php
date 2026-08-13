<?php

namespace App\Services\Crisis;

use App\Models\CrisisIncident;
use App\Models\CrisisProtocol;
use App\Models\CrisisResponseLog;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CrisisManagementService
{
    public function declareIncident(array $data): CrisisIncident
    {
        $data['uuid'] = (string) Str::uuid();
        $data['detected_at'] = now();
        $data['status'] = 'detected';
        return CrisisIncident::create($data);
    }

    public function logResponse(CrisisIncident $incident, array $data): CrisisResponseLog
    {
        $data['crisis_incident_id'] = $incident->id;
        
        if ($incident->status === 'detected') {
            $incident->update([
                'status' => 'responding',
                'responded_at' => now(),
            ]);
        }
        
        return CrisisResponseLog::create($data);
    }

    public function resolveIncident(CrisisIncident $incident, string $notes): void
    {
        $incident->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'post_mortem_notes' => $notes,
        ]);
    }

    public function getActiveProtocol(string $crisisType): ?CrisisProtocol
    {
        return CrisisProtocol::where('crisis_type', $crisisType)
            ->where('is_active', true)
            ->first();
    }

    public function calculateResponseTime(CrisisIncident $incident): int
    {
        if (!$incident->responded_at || !$incident->detected_at) return 0;
        return $incident->detected_at->diffInSeconds($incident->responded_at);
    }

    public function getIncidentTimeline(CrisisIncident $incident): Collection
    {
        return $incident->logs()->orderBy('created_at', 'asc')->get();
    }
}