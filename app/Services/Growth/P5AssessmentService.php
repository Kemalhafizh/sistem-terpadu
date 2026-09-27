<?php

namespace App\Services\Growth;

use App\Models\CharacterAssessment;
use App\Models\CharacterDimension;
use App\Models\MeritPoint;

class P5AssessmentService
{
    public function assessStudent(int $studentId, int $assessorId, int $dimensionId, array $data): CharacterAssessment
    {
        return CharacterAssessment::updateOrCreate(
            [
                'student_id' => $studentId,
                'character_dimension_id' => $dimensionId,
                'academic_year' => $data['academic_year'] ?? '2026/2027',
                'semester' => $data['semester'] ?? 'odd',
            ],
            [
                'assessor_id' => $assessorId,
                'score' => $data['score'], // 1-4 scale
                'evidence_notes' => $data['evidence_notes'] ?? null,
            ]
        );
    }

    public function awardMeritPoints(int $studentId, int $awarderId, int $points, string $reason, ?int $dimensionId = null, ?string $context = null): MeritPoint
    {
        return MeritPoint::create([
            'student_id' => $studentId,
            'awarded_by' => $awarderId,
            'character_dimension_id' => $dimensionId,
            'points' => $points,
            'reason' => $reason,
            'event_context' => $context,
        ]);
    }

    public function getStudentCharacterRadar(int $studentId, string $academicYear = '2026/2027'): array
    {
        $dimensions = CharacterDimension::where('is_active', true)->orderBy('display_order')->get();
        $assessments = CharacterAssessment::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->get();

        $radarData = [];
        foreach ($dimensions as $dim) {
            $ass = $assessments->where('character_dimension_id', $dim->id)->first();
            $radarData[] = [
                'dimension_id' => $dim->id,
                'name' => $dim->name,
                'category' => $dim->category,
                'score' => $ass ? $ass->score : 3, // Default 3 (Baik)
                'predicate' => $ass ? $this->scoreToPredicate($ass->score) : 'Baik (B)',
            ];
        }

        $totalMerit = MeritPoint::where('student_id', $studentId)->sum('points');

        return [
            'dimensions' => $radarData,
            'total_merit_points' => $totalMerit,
        ];
    }

    protected function scoreToPredicate(int $score): string
    {
        return match ($score) {
            4 => 'Sangat Baik (SB)',
            3 => 'Baik (B)',
            2 => 'Cukup (C)',
            default => 'Perlu Bimbingan (PB)',
        };
    }
}
