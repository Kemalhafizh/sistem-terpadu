<?php

namespace App\Services\Adaptive;

use App\Models\IrtItemParameter;
use App\Models\IrtStudentAbility;
use Illuminate\Support\Facades\DB;

class IrtCalibrationService
{
    public function calibrateItem(int $questionId): IrtItemParameter
    {
        // Dummy logic for IRT Calibration
        return IrtItemParameter::updateOrCreate(
            ['question_id' => $questionId],
            [
                'difficulty' => rand(-300, 300) / 100,
                'discrimination' => rand(50, 200) / 100,
                'guessing' => rand(0, 25) / 100,
                'last_calibrated_at' => now(),
            ]
        );
    }

    public function estimateStudentAbility(int $studentId, int $subjectId): float
    {
        // Dummy estimation logic
        $ability = IrtStudentAbility::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId],
            [
                'theta' => rand(-300, 300) / 100,
                'last_estimated_at' => now(),
            ]
        );
        return $ability->theta;
    }

    public function calculateProbability(float $theta, float $difficulty): float
    {
        // Rasch model: 1/(1+exp(-(theta-b)))
        return 1 / (1 + exp(-($theta - $difficulty)));
    }

    public function getItemCharacteristicCurve(int $questionId): array
    {
        $item = IrtItemParameter::where('question_id', $questionId)->first();
        if (!$item) return [];

        $curve = [];
        for ($theta = -3.0; $theta <= 3.0; $theta += 0.5) {
            $curve[] = [
                'theta' => $theta,
                'probability' => $this->calculateProbability($theta, $item->difficulty),
            ];
        }
        return $curve;
    }

    public function batchCalibrateExam(int $examId): array
    {
        // Retrieve exam questions and calibrate
        // Mock implementation
        return ['status' => 'success', 'calibrated_count' => 10];
    }
}
