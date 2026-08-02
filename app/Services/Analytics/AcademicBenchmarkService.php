<?php

namespace App\Services\Analytics;

use App\Models\AcademicBenchmark;

class AcademicBenchmarkService
{
    public function createBenchmark(int $subjectId, float $schoolAvg, float $nationalAvg, int $year): AcademicBenchmark
    {
        $percentile = $schoolAvg > $nationalAvg ? 85 : 50;

        return AcademicBenchmark::create([
            'subject_id' => $subjectId,
            'exam_type' => 'ujian_nasional',
            'school_average' => $schoolAvg,
            'national_average' => $nationalAvg,
            'percentile_rank' => $percentile,
            'year' => $year,
        ]);
    }
}
