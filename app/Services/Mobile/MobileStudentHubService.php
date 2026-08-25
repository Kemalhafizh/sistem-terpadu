<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Student;
use App\Models\TahfidzRecord;
use App\Models\User;
use Carbon\Carbon;

class MobileStudentHubService
{
    /**
     * Get tailored widgets for student mobile learning hub
     */
    public function getStudentWidgets(User $user): array
    {
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return [
                'student_name' => $user->name,
                'active_tasks' => [],
                'recent_grades' => [],
                'tahfidz_progress' => null,
            ];
        }

        // 1. Recent Grades
        $recentGrades = Grade::where('student_id', $student->id)
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($g) => [
                'subject' => $g->subject->name ?? 'Mata Pelajaran',
                'score' => $g->score,
                'type' => $g->type ?? 'UH',
            ])
            ->toArray();

        // 2. Tahfidz Progress
        $latestTahfidz = class_exists(TahfidzRecord::class) ? TahfidzRecord::where('student_id', $student->id)
            ->latest()
            ->first() : null;

        // 3. Attendance Percentage
        $attendanceCount = Attendance::where('student_id', $student->id)->count();
        $presentCount = Attendance::where('student_id', $student->id)->where('status', 'hadir')->count();
        $attendancePercentage = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100) : 100;

        return [
            'student_name' => $student->nama_lengkap ?? $user->name,
            'nis' => $student->nis ?? '-',
            'classroom' => $student->classroom->name ?? 'Kelas X',
            'attendance_rate' => $attendancePercentage,
            'recent_grades' => $recentGrades,
            'latest_tahfidz' => $latestTahfidz ? [
                'surah' => $latestTahfidz->surah_name,
                'ayah' => "Ayat {$latestTahfidz->start_ayah} - {$latestTahfidz->end_ayah}",
                'score' => $latestTahfidz->fluency_score,
            ] : null,
            'flashcards_available' => 12,
        ];
    }
}
