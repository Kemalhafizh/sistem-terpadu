<?php

namespace App\Services\Debloat;

use App\Models\AtRiskAlert;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;

class PragmaticAtRiskStudentService
{
    /**
     * Evaluate risk for a student using fast, transparent, deterministic business rules.
     * Replaces black-box ML GPU inference.
     */
    public function evaluateStudentRisk(int $studentId): ?AtRiskAlert
    {
        $student = Student::with(['user', 'classroom'])->findOrFail($studentId);

        // 1. Calculate unexcused absences (Alpha / A) in current semester
        $unexcusedAbsences = Attendance::where('student_id', $studentId)
            ->where(function ($q) {
                $q->where('status', 'A')->orWhere('status', 'ALPHA');
            })
            ->count();

        // 2. Calculate violation discipline points
        $violationPoints = StudentPointLog::where('student_id', $studentId)
            ->where(function ($q) {
                $q->where('type', 'penalty')
                  ->orWhere('type', 'VIOLATION')
                  ->orWhere('type', 'violation')
                  ->orWhere('points', '>', 0);
            })
            ->sum('points');

        // 3. Calculate current GPA / Average Score
        $grades = Grade::where('student_id', $studentId)->get();
        $currentGpa = $grades->count() > 0 ? round($grades->avg('score'), 2) : 85.0;

        $riskLevel = null;
        $triggerReasons = [];

        // Rule A: Excessive Absences
        if ($unexcusedAbsences >= 5) {
            $riskLevel = 'CRITICAL';
            $triggerReasons[] = "Alpha kehadiran tinggi ({$unexcusedAbsences}x tidak hadir tanpa keterangan)";
        } elseif ($unexcusedAbsences >= 3) {
            $riskLevel = $riskLevel === 'CRITICAL' ? 'CRITICAL' : 'HIGH';
            $triggerReasons[] = "Alpha kehadiran mencapai batas peringatan ({$unexcusedAbsences}x)";
        }

        // Rule B: High Discipline Violation Points
        if ($violationPoints >= 75) {
            $riskLevel = 'CRITICAL';
            $triggerReasons[] = "Akumulasi poin pelanggaran sangat tinggi ({$violationPoints} poin / Max 100)";
        } elseif ($violationPoints >= 40) {
            $riskLevel = $riskLevel === 'CRITICAL' ? 'CRITICAL' : 'HIGH';
            $triggerReasons[] = "Poin pelanggaran mencapai batas SP ({$violationPoints} poin)";
        }

        // Rule C: Low Academic Performance
        if ($currentGpa < 70.0) {
            $riskLevel = $riskLevel ?? 'MEDIUM';
            $triggerReasons[] = "Rata-rata nilai rapor berada di bawah KKM/KKTP ({$currentGpa})";
        }

        if (empty($triggerReasons)) {
            // Student is healthy, resolve any open alerts
            AtRiskAlert::where('student_id', $studentId)
                ->where('status', 'OPEN')
                ->update(['status' => 'RESOLVED', 'resolved_at' => now()]);

            return null;
        }

        // Auto-assign to BK Teacher
        $bkTeacher = User::where('role', 'teacher')
            ->where(function ($q) {
                $q->where('email', 'like', '%bk%')
                  ->orWhere('name', 'like', '%BK%')
                  ->orWhere('name', 'like', '%Bimbingan%');
            })->first() ?? User::where('role', 'teacher')->first();

        return AtRiskAlert::updateOrCreate(
            [
                'student_id' => $studentId,
                'status' => 'OPEN',
            ],
            [
                'risk_level' => $riskLevel ?? 'MEDIUM',
                'trigger_reason' => implode('; ', $triggerReasons),
                'unexcused_absences' => $unexcusedAbsences,
                'violation_points' => $violationPoints,
                'current_gpa' => $currentGpa,
                'assigned_bk_teacher_id' => $bkTeacher?->id,
            ]
        );
    }

    /**
     * Resolve alert with counselor notes.
     */
    public function resolveAlert(int $alertId, string $counselorNotes): AtRiskAlert
    {
        $alert = AtRiskAlert::findOrFail($alertId);
        $alert->update([
            'status' => 'RESOLVED',
            'bk_counselor_notes' => $counselorNotes,
            'resolved_at' => now(),
        ]);

        return $alert;
    }
}
