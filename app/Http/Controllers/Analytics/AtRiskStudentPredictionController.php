<?php

declare(strict_types=1);

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\StudentRiskProfile;
use App\Models\User;
use App\Services\Analytics\AtRiskStudentPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AtRiskStudentPredictionController extends Controller
{
    public function __construct(
        protected AtRiskStudentPredictionService $riskService
    ) {}

    public function analyzeStudent(Request $request, int $studentId): JsonResponse
    {
        $student = User::where('id', $studentId)->firstOrFail();

        $profile = $this->riskService->computeRiskProfile($student);

        return response()->json([
            'success' => true,
            'risk_profile' => $profile,
        ], 200);
    }

    public function getAtRiskDashboard(): JsonResponse
    {
        $profiles = StudentRiskProfile::with('student')
            ->whereIn('composite_risk_level', ['high', 'critical'])
            ->orderByDesc('predicted_dropout_probability')
            ->get();

        return response()->json([
            'success' => true,
            'at_risk_count' => $profiles->count(),
            'students' => $profiles,
        ], 200);
    }
}
