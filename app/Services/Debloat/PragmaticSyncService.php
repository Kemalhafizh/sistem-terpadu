<?php

namespace App\Services\Debloat;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\OfflineSyncQueue;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\User;
use App\Support\Access\StudentDataAccess;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PragmaticSyncService
{
    /**
     * Process sequential FIFO actions from mobile offline queue.
     * Replaces vector clocks and conflict resolvers with simple, transactional replay.
     *
     * An action is PROCESSED only when its record was actually written. Missing
     * data fails the action with a message instead of being filled in: the
     * tahfidz log used to default to Al-Mulk 1-30 with grade A and the first
     * teacher in the database, attendance to academic year/classroom id 1, and
     * TEACHING_JOURNAL or an unknown type answered "synced" without saving.
     * A FAILED action can be sent again with the same client_uuid.
     */
    public function processQueue(User $user, array $actions): array
    {
        $results = [];

        foreach ($actions as $action) {
            $clientUuid = $action['client_uuid'] ?? (string) Str::uuid();
            $actionType = $action['action_type'] ?? 'UNKNOWN';
            $payload = $action['payload'] ?? [];

            // Idempotency: only a processed action is final.
            $existing = OfflineSyncQueue::where('client_uuid', $clientUuid)->where('status', 'PROCESSED')->first();
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
                DB::transaction(fn () => $this->apply($user, $actionType, (array) $payload));

                OfflineSyncQueue::updateOrCreate(['client_uuid' => $clientUuid], [
                    'user_id' => $user->id,
                    'action_type' => $actionType,
                    'payload' => $payload,
                    'status' => 'PROCESSED',
                    'error_message' => null,
                    'processed_at' => now(),
                ]);

                $results[] = [
                    'client_uuid' => $clientUuid,
                    'action_type' => $actionType,
                    'status' => 'PROCESSED',
                    'message' => __('Sukses disinkronkan ke database server.'),
                ];
            } catch (\Throwable $e) {
                // A rejection carries a message for the user; anything else is
                // logged in the queue but not echoed back (it may be SQL).
                $message = $e instanceof DomainException ? $e->getMessage() : __('Aksi gagal diproses di server.');

                OfflineSyncQueue::updateOrCreate(['client_uuid' => $clientUuid], [
                    'user_id' => $user->id,
                    'action_type' => $actionType,
                    'payload' => $payload,
                    'status' => 'FAILED',
                    'error_message' => $e->getMessage(),
                ]);

                $results[] = [
                    'client_uuid' => $clientUuid,
                    'action_type' => $actionType,
                    'status' => 'FAILED',
                    'error' => $message,
                ];
            }
        }

        return [
            'total_submitted' => count($actions),
            'processed_count' => count(array_filter($results, fn ($r) => $r['status'] === 'PROCESSED')),
            'results' => $results,
            'synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Write one action, or throw a DomainException saying why it cannot be written.
     */
    private function apply(User $user, string $actionType, array $payload): void
    {
        if (! in_array($actionType, ['ATTENDANCE_CHECKIN', 'MUTABAAH_LOG'], true)) {
            throw new DomainException(__('Jenis aksi :type belum didukung untuk sinkronisasi.', ['type' => $actionType]));
        }

        $student = Student::where('user_id', $user->id)->first();
        if (! $student) {
            throw new DomainException(__('Data siswa untuk akun ini belum tercatat. Hubungi tata usaha sekolah.'));
        }

        if ($actionType === 'ATTENDANCE_CHECKIN') {
            $academicYearId = AcademicYear::current()?->id;
            if (! $academicYearId || ! $student->classroom_id) {
                throw new DomainException(__('Presensi belum bisa dicatat: tahun ajaran aktif atau kelas siswa belum diatur.'));
            }

            $dbStatus = match (strtoupper((string) ($payload['status'] ?? 'H'))) {
                'PRESENT', 'HADIR', 'H' => 'H',
                'SICK', 'SAKIT', 'S' => 'S',
                'PERMIT', 'IZIN', 'I' => 'I',
                default => 'A',
            };

            Attendance::updateOrCreate(
                ['student_id' => $student->id, 'date' => $payload['date'] ?? now()->toDateString()],
                [
                    'academic_year_id' => $academicYearId,
                    'classroom_id' => $student->classroom_id,
                    'status' => $dbStatus,
                    'notes' => $payload['notes'] ?? null,
                    'recorded_by' => $user->id,
                ]
            );

            return;
        }

        // MUTABAAH_LOG: a tahfidz setoran. Every value comes from the client;
        // the teacher is the one who heard it, not whoever comes first.
        $validator = Validator::make($payload, [
            'surah_number' => 'required|integer|between:1,114',
            'surah_name' => 'required|string|max:100',
            'ayah_start' => 'required|integer|min:1',
            'ayah_end' => 'required|integer|gte:ayah_start',
            'teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->whereIn('role', StudentDataAccess::TEACHER_ROLES)],
            'grade' => 'nullable|string|max:5',
        ]);
        if ($validator->fails()) {
            throw new DomainException(__('Setoran tahfidz belum lengkap: :fields.', ['fields' => implode(', ', $validator->errors()->keys())]));
        }

        TahfidzLog::create([
            'student_id' => $student->id,
            'teacher_id' => (int) $payload['teacher_id'],
            'surah_number' => (int) $payload['surah_number'],
            'surah_name' => $payload['surah_name'],
            'from_ayat' => (int) $payload['ayah_start'],
            'to_ayat' => (int) $payload['ayah_end'],
            'grade' => $payload['grade'] ?? null,
            'type' => 'ziyadah',
            'notes' => 'Mutabaah Yaumiyah',
            'date' => now()->toDateString(),
        ]);
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
            'schedules' => $schedules->map(fn ($s) => [
                'id' => $s->id,
                'subject' => $s->subject?->name ?? 'Mata Pelajaran',
                'day' => $s->day_of_week ?? 'Senin',
                'time' => "{$s->start_time} - {$s->end_time}",
            ]),
            'grades' => $grades->map(fn ($g) => [
                'subject' => $g->subject?->name ?? 'Pelajaran',
                'score' => $g->score,
                'letter' => $g->score >= 90 ? 'A' : ($g->score >= 80 ? 'B' : 'C'),
            ]),
            'announcements' => $announcements->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'date' => $a->created_at->format('d M Y'),
            ]),
            'server_time' => now()->toIso8601String(),
        ];
    }
}
