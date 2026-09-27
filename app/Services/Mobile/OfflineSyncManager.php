<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\SyncLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OfflineSyncManager
{
    /**
     * Pull delta changes since given timestamp.
     */
    public function pullDelta(User $user, ?string $sinceTimestamp = null): array
    {
        $since = $sinceTimestamp ? Carbon::parse($sinceTimestamp) : Carbon::now()->subDays(30);
        $serverTimestamp = Carbon::now()->toIso8601String();

        $student = Student::where('user_id', $user->id)->first();
        $studentId = $student ? $student->id : $user->id;

        $attendances = Attendance::where('student_id', $studentId)
            ->where('updated_at', '>=', $since)
            ->get();

        $grades = Grade::where('student_id', $studentId)
            ->where('updated_at', '>=', $since)
            ->get();

        $schedules = Schedule::where('updated_at', '>=', $since)->get();

        return [
            'server_timestamp' => $serverTimestamp,
            'delta_counts' => [
                'attendances' => $attendances->count(),
                'grades' => $grades->count(),
                'schedules' => $schedules->count(),
            ],
            'data' => [
                'attendances' => $attendances,
                'grades' => $grades,
                'schedules' => $schedules,
            ],
        ];
    }

    /**
     * Ingest and resolve offline mutation queue (Server Wins Strategy).
     */
    public function processOfflineMutations(User $user, string $deviceId, array $mutations): array
    {
        $syncedCount = 0;
        $conflictsResolved = 0;
        $results = [];

        $ay = AcademicYear::first() ?? AcademicYear::create([
            'year' => '2026/2027',
            'semester' => 'Ganjil',
            'is_active' => true,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
        ]);

        $classroom = Classroom::first() ?? Classroom::create([
            'name' => 'XII MIPA 1',
            'grade_level' => 'XII',
            'major' => 'MIPA',
            'academic_year' => '2026/2027',
        ]);

        $student = Student::where('user_id', $user->id)->first() ?? Student::create([
            'user_id' => $user->id,
            'nisn' => '9982348'.$user->id,
            'nis' => '120'.$user->id,
            'gender' => 'L',
            'classroom_id' => $classroom->id,
        ]);

        DB::beginTransaction();
        try {
            foreach ($mutations as $mutation) {
                $type = $mutation['type'] ?? 'attendance';
                $payload = $mutation['payload'] ?? [];

                if ($type === 'attendance') {
                    $date = $payload['date'] ?? now()->toDateString();
                    $existing = Attendance::where('student_id', $student->id)
                        ->where('date', $date)
                        ->first();

                    if ($existing) {
                        $conflictsResolved++;
                        $results[] = [
                            'client_id' => $mutation['client_id'] ?? null,
                            'status' => 'conflict_server_wins',
                            'server_id' => $existing->id,
                        ];
                    } else {
                        $attendance = Attendance::create([
                            'student_id' => $student->id,
                            'academic_year_id' => $ay->id,
                            'classroom_id' => $classroom->id,
                            'recorded_by' => $user->id,
                            'date' => $date,
                            'status' => 'H',
                            'notes' => 'Synced from offline queue: '.($payload['notes'] ?? ''),
                        ]);
                        $syncedCount++;
                        $results[] = [
                            'client_id' => $mutation['client_id'] ?? null,
                            'status' => 'committed',
                            'server_id' => $attendance->id,
                        ];
                    }
                }
            }

            SyncLog::create([
                'user_id' => $user->id,
                'device_id' => $deviceId,
                'sync_type' => 'mutation_queue',
                'records_synced' => $syncedCount,
                'conflicts_resolved' => $conflictsResolved,
                'status' => 'success',
                'payload_summary' => ['total_mutations' => count($mutations), 'results' => $results],
            ]);

            DB::commit();

            return [
                'success' => true,
                'records_synced' => $syncedCount,
                'conflicts_resolved' => $conflictsResolved,
                'results' => $results,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
