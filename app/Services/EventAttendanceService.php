<?php

namespace App\Services;

use App\Models\EventAttendanceEvent;
use App\Models\EventAttendanceRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EventAttendanceService
{
    /**
     * Create event and initialize draft records for targeted users.
     */
    public function createEvent(array $data): EventAttendanceEvent
    {
        return DB::transaction(function () use ($data) {
            $event = EventAttendanceEvent::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'event_date' => $data['event_date'],
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'target' => $data['target'],
                'target_ids' => $data['target_ids'] ?? [],
                'created_by' => $data['created_by'] ?? auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $userIds = [];
            $target = $data['target'];
            $targetIds = $data['target_ids'] ?? [];

            switch ($target) {
                case 'all_students':
                    $userIds = User::where('role', 'student')->pluck('id')->toArray();
                    break;

                case 'all_teachers':
                    $userIds = User::where('role', 'guru')->pluck('id')->toArray();
                    break;

                case 'specific_class':
                    if (! empty($targetIds)) {
                        $userIds = Student::whereIn('classroom_id', $targetIds)
                            ->pluck('user_id')
                            ->filter()
                            ->toArray();
                    }
                    break;

                case 'specific_group':
                    $userIds = $targetIds;
                    break;
            }

            // De-duplicate user IDs
            $userIds = array_unique($userIds);

            // Create draft entries
            $records = [];
            foreach ($userIds as $userId) {
                $records[] = [
                    'event_id' => $event->id,
                    'user_id' => $userId,
                    'status' => 'alpa',
                    'check_in_time' => null,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($records)) {
                // Chunk inserts to prevent SQL limitations in SQLite
                foreach (array_chunk($records, 100) as $chunk) {
                    EventAttendanceRecord::insert($chunk);
                }
            }

            return $event;
        });
    }

    /**
     * Save/update batch attendance records.
     */
    public function batchStoreAttendance(EventAttendanceEvent $event, array $recordsData): void
    {
        DB::transaction(function () use ($event, $recordsData) {
            foreach ($recordsData as $data) {
                if (! isset($data['user_id'])) {
                    continue;
                }

                EventAttendanceRecord::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'user_id' => $data['user_id'],
                    ],
                    [
                        'status' => $data['status'] ?? 'alpa',
                        'check_in_time' => $data['check_in_time'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ]
                );
            }
        });
    }

    /**
     * Get aggregate statistics for a student's event attendance.
     */
    public function getStudentEventAttendanceSummary(Student $student): array
    {
        $userId = $student->user_id;

        $records = EventAttendanceRecord::where('user_id', $userId)
            ->with('event')
            ->get();

        $total = $records->count();
        $hadir = $records->whereIn('status', ['hadir', 'terlambat'])->count();
        $izin = $records->whereIn('status', ['izin', 'sakit'])->count();
        $alpa = $records->where('status', 'alpa')->count();

        $rate = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

        // Group by event type
        $groupedStats = [];
        $types = ['upacara', 'sholat_dhuha', 'sholat_dzuhur', 'kultum', 'phbi', 'class_meeting', 'lainnya'];

        foreach ($types as $type) {
            $typeRecords = $records->filter(function ($r) use ($type) {
                return $r->event && $r->event->type === $type;
            });

            $tCount = $typeRecords->count();
            $tHadir = $typeRecords->whereIn('status', ['hadir', 'terlambat'])->count();
            $tRate = $tCount > 0 ? round(($tHadir / $tCount) * 100, 1) : 100; // default to 100 if no events

            $groupedStats[$type] = [
                'total' => $tCount,
                'hadir' => $tHadir,
                'rate' => $tRate,
            ];
        }

        return [
            'total' => $total,
            'hadir' => $hadir,
            'izin' => $izin,
            'alpa' => $alpa,
            'rate' => $rate,
            'types' => $groupedStats,
            'records' => $records->sortByDesc(function ($r) {
                return $r->event?->event_date?->format('Y-m-d').' '.$r->event?->start_time;
            }),
        ];
    }
}
