<?php

namespace App\Services\Growth;

use App\Models\PtcSession;
use App\Models\PtcSlot;
use App\Models\User;

class PtcSchedulerService
{
    public function createSession(array $data, int $organizerId): PtcSession
    {
        $session = PtcSession::create([
            'title' => $data['title'],
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'] ?? 'odd',
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_duration_minutes' => $data['slot_duration_minutes'] ?? 15,
            'location' => $data['location'] ?? 'Ruang Kelas / Zoom',
            'organizer_id' => $organizerId,
            'status' => 'scheduled',
            'max_slots_per_teacher' => $data['max_slots_per_teacher'] ?? 10,
        ]);

        $this->generateSlotsForTeachers($session);

        return $session;
    }

    public function generateSlotsForTeachers(PtcSession $session): void
    {
        $teachers = User::whereIn('role', ['guru', 'waka_kurikulum', 'waka_kesiswaan', 'bk'])->get();
        $duration = $session->slot_duration_minutes;

        $startTime = strtotime($session->start_time);
        $endTime = strtotime($session->end_time);

        foreach ($teachers as $t) {
            $curr = $startTime;
            while ($curr + ($duration * 60) <= $endTime) {
                PtcSlot::create([
                    'ptc_session_id' => $session->id,
                    'teacher_id' => $t->id,
                    'start_time' => date('H:i:s', $curr),
                    'end_time' => date('H:i:s', $curr + ($duration * 60)),
                    'status' => 'available',
                ]);
                $curr += ($duration * 60);
            }
        }
    }

    public function bookSlot(PtcSlot $slot, int $parentId, int $studentId, ?string $topic = null): PtcSlot
    {
        if ($slot->status !== 'available') {
            throw new \Exception('Slot konsultasi sudah dibooking atau tidak tersedia.');
        }

        $slot->update([
            'parent_id' => $parentId,
            'student_id' => $studentId,
            'topic' => $topic,
            'status' => 'booked',
        ]);

        return $slot;
    }

    public function cancelBooking(PtcSlot $slot): PtcSlot
    {
        $slot->update([
            'parent_id' => null,
            'student_id' => null,
            'topic' => null,
            'status' => 'available',
        ]);

        return $slot;
    }
}
