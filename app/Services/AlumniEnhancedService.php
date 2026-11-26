<?php

namespace App\Services;

use App\Models\AlumniData;
use App\Models\ClassEnrollment;
use App\Models\Student;
use App\Models\TracerResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlumniEnhancedService
{
    // A dictionary of common Indonesian cities with their coordinates for Leaflet.js map
    protected static array $cityCoordinates = [
        'semarang' => [-6.966667, 110.416664],
        'demak' => [-6.891111, 110.639722],
        'kudus' => [-6.80483, 110.84049],
        'pati' => [-6.7513, 111.0378],
        'jepara' => [-6.5886, 110.6669],
        'surakarta' => [-7.575489, 110.824326],
        'solo' => [-7.575489, 110.824326],
        'yogyakarta' => [-7.795577, 110.369490],
        'jogja' => [-7.795577, 110.369490],
        'jakarta' => [-6.2088, 106.8456],
        'bandung' => [-6.9175, 107.6191],
        'surabaya' => [-7.2575, 112.7521],
        'malang' => [-7.9839, 112.6214],
        'salatiga' => [-7.3305, 110.5084],
        'pekalongan' => [-6.8886, 109.6753],
        'tegal' => [-6.8676, 109.1373],
        'purwokerto' => [-7.4244, 109.2300],
        'magelang' => [-7.4797, 110.2182],
        'sragen' => [-7.4277, 111.0224],
        'karanganyar' => [-7.6277, 110.9525],
        'wonogiri' => [-7.8135, 110.9252],
        'boyolali' => [-7.5333, 110.6000],
        'klaten' => [-7.7031, 110.6025],
        'sukoharjo' => [-7.6833, 110.8333],
        'temanggung' => [-7.3000, 110.1667],
        'wonosobo' => [-7.3611, 109.9014],
        'banjarnegara' => [-7.3958, 109.6972],
        'purbalingga' => [-7.3875, 109.3678],
        'banyumas' => [-7.5147, 109.2942],
        'cilacap' => [-7.7167, 109.0167],
        'kebumen' => [-7.6711, 109.6586],
        'purworejo' => [-7.7122, 110.0078],
        'rembang' => [-6.7117, 111.3414],
        'blora' => [-6.9697, 111.4186],
        'grobogan' => [-7.0211, 110.9161],
        'purwodadi' => [-7.0211, 110.9161],
        'brebes' => [-6.8711, 109.0411],
        'batang' => [-6.9111, 109.7311],
        'kendal' => [-6.9211, 110.2011],
        'pemalang' => [-6.8911, 109.3811],
    ];

    /**
     * Graduate multiple students in bulk (also closing active ClassEnrollment as `lulus`).
     */
    public function bulkGraduateStudents(array $studentIds, int $graduationYear, ?string $skNumber = null): int
    {
        return DB::transaction(function () use ($studentIds, $graduationYear, $skNumber) {
            $count = 0;
            foreach ($studentIds as $studentId) {
                $student = Student::with('user')->find($studentId);
                if ($student && $student->status !== 'alumni') {
                    ClassEnrollment::query()
                        ->where('student_id', $student->id)
                        ->where('status', ClassEnrollment::STATUS_ACTIVE)
                        ->update([
                            'status' => ClassEnrollment::STATUS_GRADUATED,
                            'ended_on' => now()->toDateString(),
                            'ended_reason' => $skNumber ? "Lulus berdasarkan SK {$skNumber}" : "Kelulusan angkatan {$graduationYear}",
                            'decided_by' => auth()->id(),
                        ]);

                    $student->update(['status' => 'alumni']);

                    AlumniData::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'graduation_year' => $graduationYear,
                            'sk_number' => $skNumber,
                            'graduation_date' => now()->toDateString(),
                            'current_activity' => 'other',
                            'email' => $student->user?->email,
                            'phone_number' => $student->user?->phone_number ?? null,
                        ]
                    );

                    $count++;
                }
            }

            return $count;
        });
    }

    /**
     * Get alumni activity and status distribution.
     */
    public function getAlumniDistribution(): array
    {
        $activityCounts = AlumniData::select('current_activity', DB::raw('count(*) as total'))
            ->groupBy('current_activity')
            ->get()
            ->pluck('total', 'current_activity')
            ->toArray();

        $totalAlumni = AlumniData::count();
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

        // Top Institutions
        $topInstitutions = AlumniData::whereNotNull('institution_name')
            ->select('institution_name', DB::raw('count(*) as total'))
            ->groupBy('institution_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // Top Majors for Studying Alumni
        $topMajors = TracerResponse::whereNotNull('study_major')
            ->select('study_major', DB::raw('count(*) as total'))
            ->groupBy('study_major')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // Top Job Titles
        $topJobs = TracerResponse::whereNotNull('job_title')
            ->select('job_title', DB::raw('count(*) as total'))
            ->groupBy('job_title')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        return [
            'total_alumni' => $totalAlumni,
            'activity_distribution' => $activityDistribution,
            'top_institutions' => $topInstitutions,
            'top_majors' => $topMajors,
            'top_jobs' => $topJobs,
        ];
    }

    /**
     * Get geographical distribution map data of alumni.
     */
    public function getAlumniGeography(): Collection
    {
        $records = AlumniData::whereNotNull('city')
            ->select('city', 'province', 'country', DB::raw('count(*) as total'))
            ->groupBy('city', 'province', 'country')
            ->get();

        return $records->map(function ($record) {
            $cityKey = strtolower(trim($record->city));
            $coordinates = self::$cityCoordinates[$cityKey] ?? null;

            // A city without known coordinates has no pin (lat/lng null), and the
            // map lists it as unmapped. It used to be dropped at a random spot
            // around Semarang, which put alumni where they do not live.

            return [
                'city' => $record->city,
                'province' => $record->province,
                'country' => $record->country,
                'total' => $record->total,
                'lat' => $coordinates[0] ?? null,
                'lng' => $coordinates[1] ?? null,
            ];
        });
    }

    /**
     * Compare metrics of two graduation cohorts side-by-side.
     */
    public function getCohortComparison(int $yearA, int $yearB): array
    {
        return [
            'cohort_a' => $this->getCohortStats($yearA),
            'cohort_b' => $this->getCohortStats($yearB),
        ];
    }

    /**
     * Retrieve book profile collection for PDF print.
     */
    public function generateAlumniBookData(int $graduationYear): Collection
    {
        return AlumniData::where('graduation_year', $graduationYear)
            ->with(['student.user', 'student.classroom', 'tracerResponse'])
            ->orderBy('student_id')
            ->get();
    }

    /**
     * Get Tracer Study response rate status.
     */
    public function getResponseRate(int $graduationYear): array
    {
        $totalAlumni = AlumniData::where('graduation_year', $graduationYear)->count();
        $totalResponses = AlumniData::where('graduation_year', $graduationYear)
            ->whereHas('tracerResponse')
            ->count();

        $percentage = $totalAlumni > 0 ? round(($totalResponses / $totalAlumni) * 100, 1) : 0.0;

        return [
            'graduation_year' => $graduationYear,
            'total_alumni' => $totalAlumni,
            'total_responses' => $totalResponses,
            'percentage' => $percentage,
        ];
    }

    /**
     * Helper to load cohort statistics.
     */
    protected function getCohortStats(int $year): array
    {
        $total = AlumniData::where('graduation_year', $year)->count();
        $responded = AlumniData::where('graduation_year', $year)->whereHas('tracerResponse')->count();
        $responseRate = $total > 0 ? round(($responded / $total) * 100, 1) : 0;

        $activityCounts = AlumniData::where('graduation_year', $year)
            ->select('current_activity', DB::raw('count(*) as total'))
            ->groupBy('current_activity')
            ->get()
            ->pluck('total', 'current_activity')
            ->toArray();

        $activities = ['study', 'work', 'entrepreneur', 'unemployed', 'other'];
        $activityDistribution = [];
        foreach ($activities as $act) {
            $count = $activityCounts[$act] ?? 0;
            $activityDistribution[$act] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        $scores = TracerResponse::whereHas('alumni', function ($q) use ($year) {
            $q->where('graduation_year', $year);
        })->select(
            DB::raw('avg(relevance_score) as avg_relevance'),
            DB::raw('avg(satisfaction_score) as avg_satisfaction')
        )->first();

        $avgRelevance = $scores ? round((float) $scores->avg_relevance, 2) : 0;
        $avgSatisfaction = $scores ? round((float) $scores->avg_satisfaction, 2) : 0;

        return [
            'year' => $year,
            'total' => $total,
            'responded' => $responded,
            'response_rate' => $responseRate,
            'activity_distribution' => $activityDistribution,
            'avg_relevance' => $avgRelevance,
            'avg_satisfaction' => $avgSatisfaction,
        ];
    }
}
