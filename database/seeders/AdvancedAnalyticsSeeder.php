<?php

namespace Database\Seeders;

use App\Models\AcademicBenchmark;
use App\Models\EngagementMetric;
use App\Models\FinancialForecast;
use App\Models\MlPerformancePrediction;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdvancedAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('role', 'siswa')->first() ?? User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Analytics']);
        $parent = User::where('role', 'orang_tua')->first() ?? User::factory()->create(['role' => 'orang_tua', 'name' => 'Orang Tua Analytics']);
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'name' => 'Admin Finance Analytics']);
        $subject = Subject::first() ?? Subject::create(['name' => 'Matematika Umum', 'code' => 'MAT-GEN']);

        // 39.1: ML Performance Prediction
        $pred = MlPerformancePrediction::create([
            'student_id' => $student->id,
            'predicted_gpa' => 3.75,
            'risk_level' => 'low',
            'factors' => ['Kehadiran 98%', 'Nilai CBT Rata-rata 88'],
            'prediction_date' => now(),
        ]);

        // 39.2: Parent Engagement Metric
        EngagementMetric::create([
            'parent_user_id' => $parent->id,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'login_count' => 14,
            'message_replies' => 6,
            'event_attendance' => 2,
            'engagement_score' => 98.00,
        ]);

        // 39.3: Financial Forecast
        FinancialForecast::create([
            'scenario_name' => 'Proyeksi RAPBS 2026/2027 Baseline',
            'projected_revenue' => 1500000000.00,
            'projected_expense' => 1250000000.00,
            'confidence_interval' => 95.00,
            'parameters' => ['spp_growth' => '5%', 'bos_funding' => 'flat'],
            'created_by' => $admin->id,
        ]);

        // 39.4: Academic Benchmark
        AcademicBenchmark::create([
            'subject_id' => $subject->id,
            'exam_type' => 'ujian_nasional',
            'school_average' => 88.50,
            'national_average' => 76.20,
            'percentile_rank' => 92,
            'year' => 2026,
        ]);
    }
}
