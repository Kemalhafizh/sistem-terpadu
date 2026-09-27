<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\StudentPointLog;
use App\Models\StudentRiskProfile;
use App\Models\User;

class AtRiskStudentPredictionService
{
    /**
     * Compute holistic predictive risk profile for a student.
     */
    public function computeRiskProfile(User $student): StudentRiskProfile
    {
        // 1. Academic risk (Average grade vs threshold 75)
        $avgGrade = Grade::where('student_id', $student->id)->avg('score') ?? 80.0;
        $academicRisk = round(max(0.0, min(100.0, (100.0 - $avgGrade) * 1.5)), 2);

        // 2. Attendance risk (Absence rate over last 30 days)
        $totalAttendances = Attendance::where('student_id', $student->id)->count();
        $absences = Attendance::where('student_id', $student->id)->whereIn('status', ['absent', 'alpa'])->count();
        $absenceRate = $totalAttendances > 0 ? ($absences / $totalAttendances) * 100.0 : 0.0;
        $attendanceRisk = round(min(100.0, $absenceRate * 4.0), 2);

        // 3. Behavioral risk (Disciplinary points)
        $violationPoints = StudentPointLog::where('student_id', $student->id)->where('type', 'violation')->sum('points') ?? 0;
        $behavioralRisk = round(min(100.0, $violationPoints * 2.0), 2);

        // 4. Composite Risk Level
        $compositeScore = round(($academicRisk * 0.45) + ($attendanceRisk * 0.35) + ($behavioralRisk * 0.20), 2);

        $riskLevel = 'low';
        if ($compositeScore >= 75.0) {
            $riskLevel = 'critical';
        } elseif ($compositeScore >= 50.0) {
            $riskLevel = 'high';
        } elseif ($compositeScore >= 25.0) {
            $riskLevel = 'medium';
        }

        $dropoutProb = round(min(99.0, $compositeScore * 0.85), 2);

        $riskFactors = [];
        $recommendations = [];

        if ($academicRisk > 40.0) {
            $riskFactors[] = 'Nilai akademik di bawah rata-rata kelas (Rata-rata: '.round($avgGrade, 1).').';
            $recommendations[] = 'Jadwalkan sesi remedial adaptif IRT dan bimbingan belajar teman sebaya.';
        }
        if ($attendanceRisk > 30.0) {
            $riskFactors[] = 'Tingkat ketidakhadiran mencapai '.round($absenceRate, 1).'%.';
            $recommendations[] = 'Lakukan home-visit atau konfirmasi berkala dengan wali murid.';
        }
        if ($behavioralRisk > 20.0) {
            $riskFactors[] = 'Tercatat akumulasi poin pelanggaran disiplin.';
            $recommendations[] = 'Konsultasi intensif dengan Guru Bimbingan Konseling (BK).';
        }
        if (empty($riskFactors)) {
            $riskFactors[] = 'Performa siswa stabil dan berada dalam batas aman.';
            $recommendations[] = 'Pertahankan ritme belajar dan dorong partisipasi kegiatan pengayaan.';
        }

        return StudentRiskProfile::updateOrCreate(
            ['student_id' => $student->id],
            [
                'academic_risk_score' => $academicRisk,
                'attendance_risk_score' => $attendanceRisk,
                'behavioral_risk_score' => $behavioralRisk,
                'composite_risk_level' => $riskLevel,
                'predicted_dropout_probability' => $dropoutProb,
                'risk_factors' => $riskFactors,
                'intervention_recommendations' => $recommendations,
                'assessed_at' => now(),
            ]
        );
    }
}
