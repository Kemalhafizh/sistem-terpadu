<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\StudentPointLog;
use App\Notifications\StudentAttendanceNotification;
use Illuminate\Support\Facades\DB;

/**
 * Class AttendanceService
 *
 * Menangani logika bisnis yang berkaitan dengan pencatatan absensi harian
 * siswa dan integrasinya dengan log poin (seperti penalti BK).
 */
class AttendanceService
{
    /**
     * Record bulk attendance and handle point integration.
     *
     * Menyimpan data presensi secara massal untuk satu kelas pada tanggal tertentu.
     * Jika status presensi adalah 'Alpa' (A), sistem akan otomatis mencatat
     * log penalti poin BK untuk siswa tersebut.
     *
     * @throws \Exception
     */
    public function recordBulkAttendance(int $classroomId, string $date, array $attendancesData, int $recordedBy): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            throw new \Exception('Tahun ajaran aktif tidak ditemukan.');
        }

        DB::transaction(function () use ($classroomId, $date, $attendancesData, $recordedBy, $activeYear) {
            foreach ($attendancesData as $studentId => $data) {
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'classroom_id' => $classroomId,
                        'date' => $date,
                        'schedule_id' => null, // Daily attendance
                    ],
                    [
                        'academic_year_id' => $activeYear->id,
                        'status' => $data['status'],
                        'notes' => $data['notes'] ?? null,
                        'recorded_by' => $recordedBy,
                    ]
                );

                // BK POINT INTEGRATION: Auto-log penalty if Alpa (A)
                if ($attendance->wasRecentlyCreated || $attendance->wasChanged('status')) {
                    if ($data['status'] === 'A') {
                        StudentPointLog::create([
                            'student_id' => $studentId,
                            'created_by' => $recordedBy,
                            'points' => 10,
                            'type' => 'penalty',
                            'description' => 'Bolos / Tanpa Keterangan pada tanggal '.$date,
                        ]);

                        // Send Attendance Alert to Parent
                        $student = $attendance->student;
                        if ($student && $student->parent) {
                            $student->parent->notify(new StudentAttendanceNotification($attendance));
                        }
                    }
                }
            }
        });
    }
}
