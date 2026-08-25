<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiTutorSession;
use App\Services\Ai\AiPersonalizedTutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTutorController extends Controller
{
    public function __construct(
        protected AiPersonalizedTutorService $tutorService
    ) {}

    public function startSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_name' => 'required|string|max:128',
            'topic' => 'required|string|max:255',
            'difficulty_level' => 'nullable|string|in:beginner,intermediate,advanced',
            'learning_objective' => 'nullable|string',
        ]);

        $session = $this->tutorService->startSession(
            $request->user(),
            $validated['subject_name'],
            $validated['topic'],
            $validated['difficulty_level'] ?? 'intermediate',
            $validated['learning_objective'] ?? null
        );

        return response()->json([
            'success' => true,
            'session' => $session->load('messages'),
        ], 201);
    }

    public function sendMessage(Request $request, int $sessionId): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1',
        ]);

        $session = AiTutorSession::where('id', $sessionId)
            ->where('student_id', $request->user()->id)
            ->firstOrFail();

        $response = $this->tutorService->replyMessage($session, $validated['message']);

        return response()->json([
            'success' => true,
            'data' => $response,
        ], 200);
    }

    public function getHistory(Request $request, int $sessionId): JsonResponse
    {
        $session = AiTutorSession::where('id', $sessionId)
            ->where('student_id', $request->user()->id)
            ->with('messages')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'session' => $session,
        ], 200);
    }
}
