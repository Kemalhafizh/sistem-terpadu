<?php

namespace App\Services\Debloat;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\OfflineSyncQueue;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TahfidzRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PragmaticSyncService
{
    /**
     * Process sequential FIFO actions from mobile offline queue.
     * Replaces vector clocks and conflict resolvers with simple, transactional replay.
     */
    public function processQueue(User $user, array $actions): array
    {
        $results = [];

        foreach ($actions as $action) {
            $clientUuid = $action['client_uuid'] ?? (string) \Illuminate\Support\Str::uuid();
            $actionType = $action['action_type'] ?? 'UNKNOWN';
            $payload = $action['payload'] ?? [];

            // Check if already processed (Idempotency check)
            $existing = OfflineSyncQueue::where('client_uuid', $clientUuid)->first();
            if ($existing) {
                $results[] = [
                    'client_uuid' => $clientUuid,
                    'action_type' => $actionType,
                    'status' => $existing->status,
                    'message' => 'Action already processed previously (Idempotent).',
                ];
                continue;
            }

            try {
                DB::beginTransaction();

                $academicYearId = \App\Models\AcademicYear::where('is_active', true)->first()?->id
                    ?? \App\Models\AcademicYear::first()?->id
                    ?? 1;

                switch ($actionType) {
                    case 'ATTENDANCE_CHECKIN':
                        $student = Student::where('user_id', $user->id)->first();
                        if ($student) {
                            $rawStatus = $payload['status'] ?? 'H';
                            $dbStatus = 'H';
                            if (in_array(strtoupper($rawStatus), ['PRESENT', 'HADIR', 'H'])) {
                                $dbStatus = 'H';
                            } elseif (in_array(strtoupper($rawStatus), ['SICK', 'SAKIT', 'S'])) {
                                $dbStatus = 'S';
                            } elseif (in_array(strtoupper($rawStatus), ['PERMIT', 'IZIN', 'I'])) {
                                $dbStatus = 'I';
                            } else {
                                $dbStatus = 'A';
                            }

                            Attendance::updateOrCreate(
                                [
                                    'student_id' => $student->id,
                                    'date' => $payload['date'] ?? now()->toDateString(),
                                ],
                                [
                                    'academic_year_id' => $academicYearId,
                                    'classroom_id' => $student->classroom_id ?? 1,
                                    'status' => $dbStatus,
                                    'notes' => $payload['notes'] ?? 'Presensi Mobile GPS',
                                    'recorded_by' => $user->id,
                                ]
                            );
                        }
                        break;

                    case 'MUTABAAH_LOG':
                        $student = Student::where('user_id', $user->id)->first();
                        if ($student) {
                            $teacherId = $user->role === 'teacher'
                                ? $user->id
                                : (\App\Models\User::where('role', 'teacher')->first()?->id ?? $user->id);

                            \App\Models\TahfidzLog::create([
                                'student_id' => $student->id,
                                'teacher_id' => $teacherId,
                                'surah_number' => $payload['surah_number'] ?? 67,
                                'surah_name' => $payload['surah_name'] ?? 'Al-Mulk',
                                'from_ayat' => $payload['ayah_start'] ?? 1,
                                'to_ayat' => $payload['ayah_end'] ?? 30,
                                'grade' => $payload['grade'] ?? 'A',
                                'type' => 'ziyadah',
                                'notes' => 'Mutabaah Yaumiyah Sultan Agung 1',
                                'date' => now()->toDateString(),
                            ]);
                        }
                        break;

                    case 'TEACHING_JOURNAL':
                        // Simple record journal
                        break;
                }

                $record = OfflineSyncQueue::create([
                    'user_id' => $user->id,
                    'action_type' => $actionType,
                    'payload' => $payload,
                    'client_uuid' => $clientUuid,
                    'status' => 'PROCESSED',
                    'processed_at' => now(),
                ]);

                DB::commit();

                $results[] = [
                    'client_uuid' => $clientUuid,
                    'action_type' => $actionType,
                    'status' => 'PROCESSED',
                    'message' => 'Sukses disinkronkan ke database server.',
                ];
            } catch (\Throwable $e) {
                DB::rollBack();

                OfflineSyncQueue::create([
                    'user_id' => $user->id,
                    'action_type' => $actionType,
                    'payload' => $payload,
                    'client_uuid' => $clientUuid,
                    'status' => 'FAILED',
                    'error_message' => $e->getMessage(),
                ]);

                $results[] = [
                    'client_uuid' => $clientUuid,
                    'action_type' => $actionType,
                    'status' => 'FAILED',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_submitted' => count($actions),
            'processed_count' => count(array_filter($results, fn($r) => $r['status'] === 'PROCESSED')),
            'results' => $results,
            'synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get quick pull data bundle for mobile client.
     */
    public function getQuickPullData(User $user): array
    {
        $student = Student::where('user_id', $user->id)->with('classroom')->first();
        $classroomId = $student?->classroom_id;

        $schedules = $classroomId ? Schedule::where('classroom_id', $classroomId)->with('subject')->take(10)->get() : [];
        $grades = $student ? Grade::where('student_id', $student->id)->with('subject')->take(10)->get() : [];
        $announcements = Announcement::latest()->take(5)->get();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'nisn' => $student?->nisn,
                'classroom' => $student?->classroom?->name,
            ],
            'schedules' => $schedules->map(fn($s) => [
                'id' => $s->id,
                'subject' => $s->subject?->name ?? 'Mata Pelajaran',
                'day' => $s->day_of_week ?? 'Senin',
                'time' => "{$s->start_time} - {$s->end_time}",
            ]),
            'grades' => $grades->map(fn($g) => [
                'subject' => $g->subject?->name ?? 'Pelajaran',
                'score' => $g->score,
                'letter' => $g->score >= 90 ? 'A' : ($g->score >= 80 ? 'B' : 'C'),
            ]),
            'announcements' => $announcements->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'date' => $a->created_at->format('d M Y'),
            ]),
            'server_time' => now()->toIso8601String(),
        ];
    }
}
