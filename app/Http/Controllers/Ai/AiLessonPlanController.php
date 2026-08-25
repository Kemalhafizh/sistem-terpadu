<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\GeneratedLessonPlan;
use App\Services\Ai\AiLessonPlanGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiLessonPlanController extends Controller
{
    public function __construct(
        protected AiLessonPlanGeneratorService $lessonPlanService
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_name' => 'required|string|max:128',
            'grade_level' => 'required|string|max:32',
            'topic' => 'required|string|max:255',
        ]);

        $plan = $this->lessonPlanService->generateLessonPlan(
            $request->user(),
            $validated['subject_name'],
            $validated['grade_level'],
            $validated['topic']
        );

        return response()->json([
            'success' => true,
            'lesson_plan' => $plan,
        ], 201);
    }

    public function getPlans(Request $request): JsonResponse
    {
        $plans = GeneratedLessonPlan::where('teacher_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ], 200);
    }
}
