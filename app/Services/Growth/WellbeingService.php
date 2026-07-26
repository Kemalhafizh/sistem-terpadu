<?php

namespace App\Services\Growth;

use App\Models\WellbeingJournal;
use App\Models\WellbeingScreening;
use App\Models\WellbeingScreeningResponse;

class WellbeingService
{
    public function createScreening(array $data, int $creatorId): WellbeingScreening
    {
        return WellbeingScreening::create([
            'title' => $data['title'],
            'screening_type' => $data['screening_type'] ?? 'srq20',
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'] ?? 'odd',
            'target_grade' => $data['target_grade'] ?? null,
            'status' => 'draft',
            'created_by' => $creatorId,
        ]);
    }

    public function activateScreening(WellbeingScreening $screening): WellbeingScreening
    {
        $screening->update(['status' => 'active']);
        return $screening;
    }

    public function submitResponse(WellbeingScreening $screening, int $studentId, array $responsesData): WellbeingScreeningResponse
    {
        $responses = $responsesData['responses'] ?? [];
        // Calculate total score for SRQ-20 (sum of '1' answers) or PHQ-9 (sum of scores 0-3)
        $score = array_sum(array_values($responses));

        $riskLevel = $this->calculateRiskLevel($screening->screening_type, $score);
        $followupRequired = in_array($riskLevel, ['high', 'critical']);

        return WellbeingScreeningResponse::create([
            'wellbeing_screening_id' => $screening->id,
            'student_id' => $studentId,
            'responses' => $responses,
            'total_score' => $score,
            'risk_level' => $riskLevel,
            'screened_at' => now(),
            'followup_required' => $followupRequired,
        ]);
    }

    public function calculateRiskLevel(string $type, int $score): string
    {
        if ($type === 'srq20') {
            if ($score >= 12) return 'critical';
            if ($score >= 8) return 'high';
            if ($score >= 5) return 'moderate';
            return 'low';
        }

        // PHQ-9 default
        if ($score >= 20) return 'critical';
        if ($score >= 15) return 'high';
        if ($score >= 10) return 'moderate';
        return 'low';
    }

    public function addMoodJournalEntry(int $studentId, array $data): WellbeingJournal
    {
        return WellbeingJournal::create([
            'student_id' => $studentId,
            'mood_level' => $data['mood_level'],
            'journal_entry' => $data['journal_entry'] ?? null,
            'activities' => $data['activities'] ?? [],
            'sleep_hours' => $data['sleep_hours'] ?? null,
            'is_private' => $data['is_private'] ?? true,
            'created_at' => now(),
        ]);
    }

    public function getCounselorHeatmap(): array
    {
        $responses = WellbeingScreeningResponse::with(['student', 'screening'])->get();
        $total = $responses->count();

        $byRisk = [
            'low' => $responses->where('risk_level', 'low')->count(),
            'moderate' => $responses->where('risk_level', 'moderate')->count(),
            'high' => $responses->where('risk_level', 'high')->count(),
            'critical' => $responses->where('risk_level', 'critical')->count(),
        ];

        return [
            'total_screened' => $total,
            'by_risk' => $byRisk,
            'followup_pending' => $responses->where('followup_required', true)->whereNull('counselor_id')->count(),
            'responses' => $responses,
        ];
    }
}
