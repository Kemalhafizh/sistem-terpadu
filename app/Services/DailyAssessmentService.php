<?php

namespace App\Services;

use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\Student;
use App\Models\Grade;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyAssessmentService
{
    /**
     * Create a new daily assessment and initialize empty score drafts for all students in the classroom.
     */
    public function createAssessment(array $data): DailyAssessment
    {
        return DB::transaction(function () use ($data) {
            $academicYearId = $data['academic_year_id'] ?? AcademicYear::current()?->id;
            
            if (!$academicYearId) {
                throw new \Exception('Tahun akademik aktif tidak ditemukan.');
            }

            $assessment = DailyAssessment::create([
                'subject_id' => $data['subject_id'],
                'classroom_id' => $data['classroom_id'],
                'teacher_id' => $data['teacher_id'],
                'academic_year_id' => $academicYearId,
                'title' => $data['title'],
                'type' => $data['type'],
                'assessment_date' => $data['assessment_date'],
                'max_score' => $data['max_score'] ?? 100,
                'kkm' => $data['kkm'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            // Fetch all students in the classroom
            $students = Student::where('classroom_id', $data['classroom_id'])->get();

            // Create blank draft scores for each student
            foreach ($students as $student) {
                DailyAssessmentScore::create([
                    'daily_assessment_id' => $assessment->id,
                    'student_id' => $student->id,
                    'score' => null,
                    'needs_remedial' => false,
                    'remedial_status' => 'not_needed',
                ]);
            }

            return $assessment;
        });
    }

    /**
     * Store score entries for an assessment in batch and run auto-remedial check.
     */
    public function batchStoreScores(int $assessmentId, array $scores): void
    {
        DB::transaction(function () use ($assessmentId, $scores) {
            foreach ($scores as $studentId => $scoreData) {
                $score = isset($scoreData['score']) && $scoreData['score'] !== '' ? (float) $scoreData['score'] : null;
                $notes = $scoreData['notes'] ?? null;

                DailyAssessmentScore::updateOrCreate(
                    [
                        'daily_assessment_id' => $assessmentId,
                        'student_id' => $studentId,
                    ],
                    [
                        'score' => $score,
                        'notes' => $notes,
                    ]
                );
            }

            $this->autoDetectRemedial($assessmentId);
        });
    }

    /**
     * Evaluate scores against KKM, marking students for remedial if necessary.
     */
    public function autoDetectRemedial(int $assessmentId): int
    {
        $assessment = DailyAssessment::findOrFail($assessmentId);
        $kkm = $assessment->effective_kkm;
        $scores = DailyAssessmentScore::where('daily_assessment_id', $assessmentId)->get();
        $remedialCount = 0;

        foreach ($scores as $scoreRecord) {
            if ($scoreRecord->score !== null && $scoreRecord->score < $kkm) {
                $scoreRecord->update([
                    'needs_remedial' => true,
                    'remedial_status' => $scoreRecord->remedial_status === 'not_needed' ? 'pending' : $scoreRecord->remedial_status,
                ]);
                $remedialCount++;
            } else {
                // If they passed, clear pending remedial flag unless they already completed it
                if ($scoreRecord->remedial_status !== 'completed') {
                    $scoreRecord->update([
                        'needs_remedial' => false,
                        'remedial_status' => 'not_needed',
                        'remedial_score' => null,
                    ]);
                }
            }
        }

        return $remedialCount;
    }

    /**
     * Store remedial score and mark as completed.
     */
    public function storeRemedial(int $scoreId, float $remedialScore, ?string $notes = null): void
    {
        $scoreRecord = DailyAssessmentScore::findOrFail($scoreId);
        $scoreRecord->update([
            'remedial_score' => $remedialScore,
            'remedial_status' => 'completed',
            'notes' => $notes ?? $scoreRecord->notes,
        ]);
    }

    /**
     * Get analytics for a subject in a classroom.
     */
    public function getClassAnalytics(int $classroomId, int $subjectId): array
    {
        $assessments = DailyAssessment::where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->with('scores')
            ->get();

        if ($assessments->isEmpty()) {
            return [
                'has_data' => false,
                'average_score' => 0,
                'pass_rate' => 0,
                'lowest_assessment' => null,
                'assessments_trend' => [],
                'distribution' => [0, 0, 0, 0, 0],
            ];
        }

        $allFinalScores = collect();
        $assessmentsTrend = [];
        $lowestAverage = 101;
        $lowestAssessmentName = null;

        foreach ($assessments as $assessment) {
            $kkm = $assessment->effective_kkm;
            $asScores = $assessment->scores->filter(fn($s) => $s->final_score !== null);
            
            if ($asScores->isEmpty()) {
                continue;
            }

            $asAverage = round($asScores->avg('final_score'), 1);
            $assessmentsTrend[] = [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'average' => $asAverage,
                'date' => $assessment->assessment_date->format('Y-m-d'),
            ];

            if ($asAverage < $lowestAverage) {
                $lowestAverage = $asAverage;
                $lowestAssessmentName = $assessment->title;
            }

            foreach ($asScores as $s) {
                $allFinalScores->push([
                    'score' => $s->final_score,
                    'passed' => $s->final_score >= $kkm,
                ]);
            }
        }

        if ($allFinalScores->isEmpty()) {
            return [
                'has_data' => false,
                'average_score' => 0,
                'pass_rate' => 0,
                'lowest_assessment' => null,
                'assessments_trend' => [],
                'distribution' => [0, 0, 0, 0, 0],
            ];
        }

        $overallAverage = round($allFinalScores->avg('score'), 1);
        $totalStudents = $allFinalScores->count();
        $passedCount = $allFinalScores->where('passed', true)->count();
        $passRate = round(($passedCount / $totalStudents) * 100, 1);

        // Score brackets for distribution histogram:
        // [0-59, 60-69, 70-79, 80-89, 90-100]
        $distribution = [0, 0, 0, 0, 0];
        foreach ($allFinalScores as $fs) {
            $val = $fs['score'];
            if ($val >= 90) $distribution[4]++;
            elseif ($val >= 80) $distribution[3]++;
            elseif ($val >= 70) $distribution[2]++;
            elseif ($val >= 60) $distribution[1]++;
            else $distribution[0]++;
        }

        return [
            'has_data' => true,
            'average_score' => $overallAverage,
            'pass_rate' => $passRate,
            'lowest_assessment' => $lowestAssessmentName ? [
                'title' => $lowestAssessmentName,
                'average' => $lowestAverage,
            ] : null,
            'assessments_trend' => $assessmentsTrend,
            'distribution' => $distribution,
        ];
    }

    /**
     * Get transcript of daily assessments for a student.
     */
    public function getStudentTranscript(int $studentId): Collection
    {
        $activeYear = AcademicYear::current();
        return DailyAssessmentScore::where('student_id', $studentId)
            ->whereHas('dailyAssessment', function ($q) use ($activeYear) {
                if ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                }
            })
            ->with(['dailyAssessment.subject', 'dailyAssessment.teacher'])
            ->get();
    }

    /**
     * Calculate final composite grade of student for a subject (Formatif + Sumatif).
     * Weighting: Formative (Daily Assessments) = 60%, UTS = 20%, PAS = 20%.
     */
    public function calculateFinalGrade(int $studentId, int $subjectId): float
    {
        $activeYear = AcademicYear::current();
        $activeYearId = $activeYear?->id;

        // 1. Formative average
        $formativeScores = DailyAssessmentScore::where('student_id', $studentId)
            ->whereHas('dailyAssessment', function ($q) use ($subjectId, $activeYearId) {
                $q->where('subject_id', $subjectId);
                if ($activeYearId) {
                    $q->where('academic_year_id', $activeYearId);
                }
            })
            ->get();

        $formativeAverage = null;
        $validFormative = $formativeScores->filter(fn($s) => $s->final_score !== null);
        if ($validFormative->isNotEmpty()) {
            $formativeAverage = (float) $validFormative->avg('final_score');
        }

        // 2. Sumative (UTS/PAS) from Grade model
        $utsScore = null;
        $pasScore = null;

        if ($activeYearId) {
            $utsGrade = Grade::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $activeYearId)
                ->where('type', 'uts')
                ->first();
            $pasGrade = Grade::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $activeYearId)
                ->where('type', 'pas')
                ->first();
                
            if ($utsGrade) $utsScore = (float) $utsGrade->score;
            if ($pasGrade) $pasScore = (float) $pasGrade->score;
        }

        // Calculate weighted average dynamically based on available data
        $components = [];
        if ($formativeAverage !== null) $components[] = ['score' => $formativeAverage, 'weight' => 0.6];
        if ($utsScore !== null) $components[] = ['score' => $utsScore, 'weight' => 0.2];
        if ($pasScore !== null) $components[] = ['score' => $pasScore, 'weight' => 0.2];

        if (empty($components)) {
            // Default fallback is KKM or 0.0
            return 0.0;
        }

        $totalWeight = 0.0;
        $weightedSum = 0.0;
        foreach ($components as $c) {
            $totalWeight += $c['weight'];
            $weightedSum += $c['score'] * $c['weight'];
        }

        return round($weightedSum / $totalWeight, 1);
    }
}
