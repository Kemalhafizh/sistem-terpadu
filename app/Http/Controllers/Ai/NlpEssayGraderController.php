<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\EssaySubmission;
use App\Services\Ai\NlpEssayGraderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NlpEssayGraderController extends Controller
{
    public function __construct(
        protected NlpEssayGraderService $graderService
    ) {}

    public function submitEssay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assignment_title' => 'required|string|max:255',
            'essay_text' => 'required|string|min:20',
        ]);

        $submission = $this->graderService->gradeEssay(
            $request->user(),
            $validated['assignment_title'],
            $validated['essay_text']
        );

        return response()->json([
            'success' => true,
            'submission' => $submission,
        ], 201);
    }

    public function getSubmission(Request $request, int $id): JsonResponse
    {
        $submission = EssaySubmission::where('id', $id)
            ->where('student_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'submission' => $submission,
        ], 200);
    }
}
