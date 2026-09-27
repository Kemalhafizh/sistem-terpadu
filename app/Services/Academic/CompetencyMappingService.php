<?php

namespace App\Services\Academic;

use App\Models\AtpMapping;
use App\Models\CompetencyAchievement;
use App\Models\LearningObjective;
use App\Models\LearningOutcome;

class CompetencyMappingService
{
    public function createOutcome(array $data): LearningOutcome
    {
        return LearningOutcome::create([
            'subject_id' => $data['subject_id'],
            'phase' => $data['phase'] ?? 'E',
            'element' => $data['element'],
            'cp_code' => $data['cp_code'],
            'cp_description' => $data['cp_description'],
        ]);
    }

    public function createObjective(LearningOutcome $outcome, array $data): LearningObjective
    {
        return LearningObjective::create([
            'learning_outcome_id' => $outcome->id,
            'tp_code' => $data['tp_code'],
            'tp_description' => $data['tp_description'],
            'semester' => $data['semester'] ?? 'ganjil',
            'sequence_order' => $data['sequence_order'] ?? 1,
            'assessment_type' => $data['assessment_type'] ?? 'formatif',
            'bloom_taxonomy_level' => $data['bloom_taxonomy_level'] ?? 'apply',
        ]);
    }

    public function mapToAtp(LearningObjective $objective, array $data): AtpMapping
    {
        return AtpMapping::create([
            'learning_objective_id' => $objective->id,
            'week_number' => $data['week_number'],
            'teaching_hours' => $data['teaching_hours'] ?? 2,
            'learning_activities' => $data['learning_activities'],
            'assessment_method' => $data['assessment_method'],
            'resources' => $data['resources'] ?? null,
        ]);
    }

    public function assessStudentCompetency(int $studentId, int $objectiveId, string $status, int $assessorId, ?string $notes = null): CompetencyAchievement
    {
        return CompetencyAchievement::updateOrCreate(
            [
                'student_id' => $studentId,
                'learning_objective_id' => $objectiveId,
                'academic_year' => '2026/2027',
            ],
            [
                'status' => $status,
                'evidence_notes' => $notes,
                'assessed_by' => $assessorId,
                'assessed_at' => now(),
            ]
        );
    }

    public function getClassCompetencyHeatmap(int $subjectId): array
    {
        $outcomes = LearningOutcome::with('objectives.achievements.student')->where('subject_id', $subjectId)->get();

        return [
            'outcomes' => $outcomes,
            'total_objectives' => $outcomes->pluck('objectives')->flatten()->count(),
        ];
    }
}
