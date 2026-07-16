<?php

namespace App\Services;

use App\Models\AlumniData;
use App\Models\TracerResponse;
use Illuminate\Support\Facades\DB;

class AlumniService
{
    /**
     * Submit or update a tracer study response for an alumni.
     */
    public function submitTracerStudy(AlumniData $alumni, array $data): TracerResponse
    {
        return DB::transaction(function () use ($alumni, $data) {
            // Update the alumni_data fields
            $alumni->update([
                'current_activity' => $data['current_activity'] ?? 'other',
                'institution_name' => $data['institution_name'] ?? null,
            ]);

            // Save or update the tracer response
            $response = TracerResponse::updateOrCreate(
                ['alumni_data_id' => $alumni->id],
                [
                    'job_title' => $data['job_title'] ?? null,
                    'salary_range' => $data['salary_range'] ?? null,
                    'study_major' => $data['study_major'] ?? null,
                    'relevance_score' => $data['relevance_score'] ?? 5,
                    'satisfaction_score' => $data['satisfaction_score'] ?? 5,
                    'suggestions' => $data['suggestions'] ?? null,
                    'employer_name' => $data['employer_name'] ?? null,
                    'entry_year' => $data['entry_year'] ?? null,
                ]
            );

            return $response;
        });
    }

    /**
     * Get aggregated statistical summary for waka humas / admin dashboard.
     */
    public function getAnalyticsSummary(): array
    {
        // 1. Sebaran Aktivitas Alumni
        $activityCounts = AlumniData::select('current_activity', DB::raw('count(*) as total'))
            ->groupBy('current_activity')
            ->get()
            ->pluck('total', 'current_activity')
            ->toArray();

        $totalAlumni = array_sum($activityCounts);
        $activities = ['study', 'work', 'entrepreneur', 'unemployed', 'other'];
        $activityDistribution = [];
        foreach ($activities as $act) {
            $count = $activityCounts[$act] ?? 0;
            $percentage = $totalAlumni > 0 ? round(($count / $totalAlumni) * 100, 1) : 0;
            $activityDistribution[$act] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        // 2. Rata-rata Skor Relevansi & Kepuasan
        $scores = TracerResponse::select(
            DB::raw('avg(relevance_score) as avg_relevance'),
            DB::raw('avg(satisfaction_score) as avg_satisfaction'),
            DB::raw('count(*) as total_responses')
        )->first();

        $avgRelevance = $scores ? round((float) $scores->avg_relevance, 2) : 0;
        $avgSatisfaction = $scores ? round((float) $scores->avg_satisfaction, 2) : 0;
        $totalResponses = $scores ? (int) $scores->total_responses : 0;

        // 3. Sebaran Pendapatan (Salary Ranges)
        $salaryCounts = TracerResponse::whereNotNull('salary_range')
            ->select('salary_range', DB::raw('count(*) as total'))
            ->groupBy('salary_range')
            ->get()
            ->pluck('total', 'salary_range')
            ->toArray();

        $totalSalaries = array_sum($salaryCounts);
        $salaryRanges = ['< 3M', '3M - 5M', '5M - 10M', '> 10M'];
        $salaryDistribution = [];
        foreach ($salaryRanges as $range) {
            $count = $salaryCounts[$range] ?? 0;
            $percentage = $totalSalaries > 0 ? round(($count / $totalSalaries) * 100, 1) : 0;
            $salaryDistribution[$range] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        // 4. Tren Kelulusan Tahunan
        $graduationTrends = AlumniData::join('tracer_responses', 'alumni_data.id', '=', 'tracer_responses.alumni_data_id')
            ->select('alumni_data.graduation_year', DB::raw('count(*) as total'))
            ->groupBy('alumni_data.graduation_year')
            ->orderBy('alumni_data.graduation_year', 'asc')
            ->get()
            ->pluck('total', 'graduation_year')
            ->toArray();

        return [
            'total_alumni' => $totalAlumni,
            'total_responses' => $totalResponses,
            'activity_distribution' => $activityDistribution,
            'avg_relevance' => $avgRelevance,
            'avg_satisfaction' => $avgSatisfaction,
            'salary_distribution' => $salaryDistribution,
            'graduation_trends' => $graduationTrends,
        ];
    }
}
