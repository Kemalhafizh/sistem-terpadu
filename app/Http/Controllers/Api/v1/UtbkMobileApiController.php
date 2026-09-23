<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\UtbkService;
use Exception;
use Illuminate\Http\Request;

class UtbkMobileApiController extends Controller
{
    protected UtbkService $utbkService;

    public function __construct(UtbkService $utbkService)
    {
        $this->utbkService = $utbkService;
    }

    /**
     * GET /api/v1/utbk/tryouts
     */
    public function index(Request $request)
    {
        $studentId = $request->user()?->student?->id;
        $tryouts = $this->utbkService->getAvailableTryouts($studentId);
        return response()->json($tryouts);
    }

    /**
     * GET /api/v1/utbk/tryouts/{id}/questions
     */
    public function questions(Request $request, $id)
    {
        try {
            $questions = $this->utbkService->getTryoutQuestions($id);
            return response()->json($questions);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * POST /api/v1/utbk/tryouts/{id}/submit
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $student = $request->user()?->student;
        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Profil siswa tidak ditemukan untuk akun ini.',
            ], 422);
        }

        try {
            $studentId = $student->id;
            $attempt = $this->utbkService->submitTryout($id, $studentId, $request->answers);

            return response()->json([
                'success' => true,
                'message' => 'Tryout berhasil disubmit.',
                'data' => [
                    'score_tps' => $attempt->score_tps,
                    'score_literasi' => $attempt->score_literasi,
                    'score_penalaran' => $attempt->score_penalaran,
                    'total_score' => $attempt->total_score,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/v1/utbk/recommendations
     */
    public function ptnRecommendations(Request $request)
    {
        $student = $request->user()?->student;
        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Profil siswa tidak ditemukan untuk akun ini.',
            ], 422);
        }

        try {
            $studentId = $student->id;
            $recommendations = $this->utbkService->getRecommendations($studentId);
            return response()->json($recommendations);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/v1/utbk/alumni-directory
     */
    public function alumniDirectory(Request $request)
    {
        $directory = $this->utbkService->getAlumniDirectory();
        return response()->json($directory);
    }
}
