<?php

namespace App\Services\Growth;

use App\Models\StudentLifecycleEvent;
use App\Models\StudentLifecycleMilestone;
use App\Models\User;

class StudentLifecycleService
{
    public function recordEvent(int $studentId, string $eventType, string $toStatus, int $triggerId, ?string $fromStatus = null, ?array $meta = null, ?string $notes = null): StudentLifecycleEvent
    {
        return StudentLifecycleEvent::create([
            'student_id' => $studentId,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'academic_year' => '2026/2027',
            'semester' => 'odd',
            'triggered_by' => $triggerId,
            'metadata' => $meta,
            'notes' => $notes,
        ]);
    }

    public function recordMilestone(int $studentId, string $type, string $desc): StudentLifecycleMilestone
    {
        return StudentLifecycleMilestone::create([
            'student_id' => $studentId,
            'milestone_type' => $type,
            'achieved_at' => now(),
            'description' => $desc,
        ]);
    }

    public function getStudentTimeline(int $studentId): array
    {
        $events = StudentLifecycleEvent::with('triggerUser')->where('student_id', $studentId)->latest()->get();
        $milestones = StudentLifecycleMilestone::where('student_id', $studentId)->latest()->get();

        return [
            'events' => $events,
            'milestones' => $milestones,
            'current_status' => $events->first()?->to_status ?? 'enrolled',
        ];
    }

    public function calculateCohortRetention(): array
    {
        $totalStudents = User::where('role', 'student')->count();
        $activeStudents = User::where('role', 'student')->where('is_active', true)->count();
        $graduated = StudentLifecycleEvent::where('event_type', 'graduated')->count();
        $dropouts = StudentLifecycleEvent::where('event_type', 'dropped_out')->count();

        $retentionRate = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 100;

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'graduated_count' => $graduated,
            'dropout_count' => $dropouts,
            'retention_rate' => $retentionRate,
        ];
    }
}
