<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;

class MobileDashboardBuilderService
{
    /**
     * Get tailored widgets for teacher mobile dashboard
     */
    public function getTeacherWidgets(User $user): array
    {
        $today = Carbon::today()->toDateString();
        $employee = Employee::where('user_id', $user->id)->first();

        // 1. Today's Teaching Schedule
        $schedules = [];
        if (class_exists(Schedule::class)) {
            $schedules = Schedule::where('teacher_id', $user->id)
                ->where('day', Carbon::now()->locale('id')->isoFormat('dddd'))
                ->orderBy('start_time')
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'subject' => $s->subject->name ?? 'Mata Pelajaran',
                    'class' => $s->classroom->name ?? 'Kelas',
                    'time' => substr((string) $s->start_time, 0, 5).' - '.substr((string) $s->end_time, 0, 5),
                    'room' => $s->room->name ?? 'Ruang Kelas',
                ])
                ->toArray();
        }

        // 2. Attendance summary for today
        $attendancePendingCount = Classroom::where('teacher_id', $user->id)->count();

        // 3. School Announcements
        $announcements = Announcement::latest()->take(3)->get()->map(fn ($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'time_ago' => $a->created_at->diffForHumans(),
        ])->toArray();

        return [
            'teacher_name' => $user->name,
            'date_formatted' => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y'),
            'schedules' => $schedules,
            'attendance_pending_classes' => $attendancePendingCount,
            'announcements' => $announcements,
            'quick_actions' => [
                ['label' => 'Absensi Cepat', 'route' => 'mobile.teacher.attendance', 'icon' => 'user-check'],
                ['label' => 'Input Nilai', 'route' => 'mobile.teacher.grading', 'icon' => 'award'],
                ['label' => 'Jurnal Mengajar', 'route' => 'mobile.teacher.journal', 'icon' => 'book-open'],
            ],
        ];
    }
}
