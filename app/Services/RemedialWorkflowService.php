<?php

namespace App\Services;

use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\RemedialSession;
use Illuminate\Support\Str;

class RemedialWorkflowService
{
    public function autoAssignRemedial(int $assessmentId, int $assignedBy, ?string $deadline = null): array
    {
        $assessment = DailyAssessment::with('scores')->findOrFail($assessmentId);
        $kkm = (float) $assessment->kkm;

        $underKkmScores = DailyAssessmentScore::where('daily_assessment_id', $assessmentId)
            ->where('score', '<', $kkm)
            ->get();

        $assignedCount = 0;
        foreach ($underKkmScores as $scoreItem) {
            $studentUserId = \App\Models\Student::find($scoreItem->student_id)?->user_id ?? $scoreItem->student_id;
            RemedialSession::updateOrCreate(
                [
                    'daily_assessment_id' => $assessmentId,
                    'student_id' => $studentUserId,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'original_score' => $scoreItem->score,
                    'kkm' => $kkm,
                    'remedial_type' => 'tugas_tambahan',
                    'status' => 'pending',
                    'deadline' => $deadline ? date('Y-m-d H:i:s', strtotime($deadline)) : now()->addDays(7),
                    'assigned_by' => $assignedBy,
                    'teacher_notes' => "Perbaikan capaian kompetensi materi {$assessment->title}",
                ]
            );
            $assignedCount++;
        }

        return [
            'total_students_scored' => DailyAssessmentScore::where('daily_assessment_id', $assessmentId)->count(),
            'total_under_kkm' => $underKkmScores->count(),
            'assigned_to_remedial' => $assignedCount,
            'kkm' => $kkm,
        ];
    }

    public function submitRemedialScore(int $sessionId, float $score, ?string $teacherNotes = null): RemedialSession
    {
        $session = RemedialSession::findOrFail($sessionId);
        $session->update([
            'remedial_score' => $score,
            'status' => 'completed',
            'teacher_notes' => $teacherNotes ?: $session->teacher_notes,
        ]);

        return $session;
    }

    public function getFinalScore(int $studentId, int $assessmentId): float
    {
        $original = DailyAssessmentScore::where('daily_assessment_id', $assessmentId)
            ->where('student_id', $studentId)
            ->value('score') ?? 0.0;

        $remedial = RemedialSession::where('daily_assessment_id', $assessmentId)
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->first();

        if ($remedial && $remedial->remedial_score !== null) {
            // Sesuai kaidah Kurikulum Merdeka/K13: Nilai remedial maksimal setara KKM
            $cappedRemedial = min((float) $remedial->remedial_score, (float) $remedial->kkm);
            return max((float) $original, $cappedRemedial);
        }

        return (float) $original;
    }

    public function getStudentRemedials(int $studentId)
    {
        return RemedialSession::with(['assessment.subject', 'assigner'])
            ->where('student_id', $studentId)
            ->where('status', '!=', 'waived')
            ->latest()
            ->get();
    }

    public function getTeacherRemedialDashboard(int $teacherId): array
    {
        $assessments = DailyAssessment::where('teacher_id', $teacherId)->pluck('id');

        $totalSessions = RemedialSession::whereIn('daily_assessment_id', $assessments)->count();
        $pendingSessions = RemedialSession::whereIn('daily_assessment_id', $assessments)->where('status', 'pending')->count();
        $completedSessions = RemedialSession::whereIn('daily_assessment_id', $assessments)->where('status', 'completed')->count();

        $complianceRate = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 100;

        return [
            'total_remedial_cases' => $totalSessions,
            'pending_cases' => $pendingSessions,
            'completed_cases' => $completedSessions,
            'completion_rate' => $complianceRate,
        ];
    }
}
