<?php

namespace App\Services\Academic;

use App\Models\VirtualClassroom;
use App\Models\VirtualClassroomAttendance;
use Illuminate\Support\Str;

class VirtualClassroomService
{
    public function createMeeting(array $data, int $hostId): VirtualClassroom
    {
        $provider = $data['provider'] ?? 'google_meet';
        $meetingUrl = $data['meeting_url'] ?? ($provider === 'jitsi' ? 'https://meet.jit.si/' . Str::random(10) : 'https://meet.google.com/' . Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3));

        return VirtualClassroom::create([
            'title' => $data['title'],
            'provider' => $provider,
            'meeting_id' => $data['meeting_id'] ?? Str::random(9),
            'meeting_url' => $meetingUrl,
            'meeting_password' => $data['meeting_password'] ?? null,
            'host_id' => $hostId,
            'start_time' => $data['start_time'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'status' => 'scheduled',
        ]);
    }

    public function recordAttendance(VirtualClassroom $meeting, int $userId): VirtualClassroomAttendance
    {
        $att = VirtualClassroomAttendance::create([
            'virtual_classroom_id' => $meeting->id,
            'user_id' => $userId,
            'joined_at' => now(),
        ]);

        $meeting->increment('attendees_count');
        if ($meeting->status === 'scheduled') {
            $meeting->update(['status' => 'live']);
        }

        return $att;
    }
}
