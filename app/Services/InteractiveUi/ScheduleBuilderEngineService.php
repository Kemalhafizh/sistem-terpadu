<?php

declare(strict_types=1);

namespace App\Services\InteractiveUi;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleBuilderDraft;
use App\Models\Subject;
use App\Models\User;

class ScheduleBuilderEngineService
{
    /**
     * Save or update draft schedule
     */
    public function saveDraft(User $user, array $data, ?int $draftId = null): ScheduleBuilderDraft
    {
        $conflicts = $this->detectConflicts($data['schedule_data'] ?? []);
        $payload = [
            'academic_year_id' => $data['academic_year_id'] ?? null,
            'name' => $data['name'] ?? 'Draft Jadwal Baru',
            'schedule_data' => $data['schedule_data'] ?? [],
            'conflict_reports' => $conflicts,
            'status' => count($conflicts) > 0 ? 'draft' : 'approved',
            'created_by' => $user->id,
        ];

        if ($draftId) {
            $draft = ScheduleBuilderDraft::find($draftId);
            if ($draft) {
                $draft->update($payload);
                return $draft;
            }
        }

        return ScheduleBuilderDraft::create($payload);
    }

    /**
     * Detect schedule conflicts (Teacher double-booking or Room double-booking)
     */
    public function detectConflicts(array $scheduleEntries): array
    {
        $conflicts = [];
        $teacherOccupancy = []; // [day_slot_teacher => entry_index]
        $roomOccupancy = [];    // [day_slot_room => entry_index]

        foreach ($scheduleEntries as $idx => $entry) {
            $day = $entry['day'] ?? 'Senin';
            $slot = $entry['time_slot'] ?? '07:00-08:30';
            $teacherId = $entry['teacher_id'] ?? null;
            $roomId = $entry['room_id'] ?? null;
            $className = $entry['classroom_name'] ?? 'Kelas';

            if ($teacherId) {
                $teacherKey = "{$day}_{$slot}_{$teacherId}";
                if (isset($teacherOccupancy[$teacherKey])) {
                    $conflicts[] = [
                        'type' => 'TEACHER_CONFLICT',
                        'message' => "Guru ID {$teacherId} memiliki jadwal bentrok pada {$day}, slot {$slot} di {$className}.",
                        'entries' => [$teacherOccupancy[$teacherKey], $idx],
                    ];
                } else {
                    $teacherOccupancy[$teacherKey] = $idx;
                }
            }

            if ($roomId) {
                $roomKey = "{$day}_{$slot}_{$roomId}";
                if (isset($roomOccupancy[$roomKey])) {
                    $conflicts[] = [
                        'type' => 'ROOM_CONFLICT',
                        'message' => "Ruangan ID {$roomId} digunakan bersamaan pada {$day}, slot {$slot}.",
                        'entries' => [$roomOccupancy[$roomKey], $idx],
                    ];
                } else {
                    $roomOccupancy[$roomKey] = $idx;
                }
            }
        }

        return $conflicts;
    }

    /**
     * Publish draft to live Schedule table
     */
    public function publishSchedule(ScheduleBuilderDraft $draft): bool
    {
        $entries = $draft->schedule_data ?? [];
        if (empty($entries)) {
            return false;
        }

        foreach ($entries as $item) {
            if (isset($item['subject_id'], $item['teacher_id'], $item['classroom_id'])) {
                Schedule::updateOrCreate(
                    [
                        'classroom_id' => $item['classroom_id'],
                        'day' => $item['day'] ?? 'Senin',
                        'start_time' => $item['start_time'] ?? '07:00:00',
                    ],
                    [
                        'subject_id' => $item['subject_id'],
                        'teacher_id' => $item['teacher_id'],
                        'room_id' => $item['room_id'] ?? null,
                        'end_time' => $item['end_time'] ?? '08:30:00',
                    ]
                );
            }
        }

        $draft->update(['status' => 'published']);
        return true;
    }
}
