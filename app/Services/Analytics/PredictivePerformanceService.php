<?php

namespace App\Services\Analytics;

use App\Models\MlInterventionLog;
use App\Models\MlPerformancePrediction;

class PredictivePerformanceService
{
    public function generatePredictions(int $studentId, float $predictedGpa, string $riskLevel = 'low', array $factors = []): MlPerformancePrediction
    {
        return MlPerformancePrediction::create([
            'student_id' => $studentId,
            'predicted_gpa' => $predictedGpa,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'prediction_date' => now(),
        ]);
    }

    public function logIntervention(int $predictionId, int $counselorId, string $actionTaken): MlInterventionLog
    {
        return MlInterventionLog::create([
            'prediction_id' => $predictionId,
            'counselor_id' => $counselorId,
            'action_taken' => $actionTaken,
            'status' => 'in_progress',
        ]);
    }

    public function getHighRiskStudents()
    {
        return MlPerformancePrediction::with(['student', 'interventions'])->where('risk_level', 'high')->get();
    }
}
