<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use Exception;
use Illuminate\Http\Request;

class EvaluationMobileApiController extends Controller
{
    protected EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * GET /api/v1/evaluations/teachers
     */
    public function teacherList(Request $request)
    {
        $teachers = $this->evaluationService->getTeachersToEvaluate();

        return response()->json($teachers); // return array directly for Retrofit
    }

    /**
     * POST /api/v1/evaluations/teachers/submit
     */
    public function submitTeacherEval(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
        ]);

        try {
            $evaluatorId = $request->user()->id ?? null;
            $this->evaluationService->submitTeacherEvaluation(
                $request->teacher_id,
                $request->only(['rating', 'comments']),
                $evaluatorId
            );

            return response()->json([
                'success' => true,
                'message' => 'Evaluasi guru berhasil disubmit secara anonim.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/v1/evaluations/facilities
     */
    public function facilityList(Request $request)
    {
        $facilities = $this->evaluationService->getFacilities();

        return response()->json($facilities);
    }

    /**
     * POST /api/v1/evaluations/facilities/submit
     */
    public function submitFacilityEval(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facility_assets,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'is_anonymous' => 'boolean',
        ]);

        try {
            $isAnonymous = $request->input('is_anonymous', true);
            $userId = $isAnonymous ? null : ($request->user()->id ?? null);

            $this->evaluationService->submitFacilityEvaluation(
                $request->facility_id,
                $request->only(['rating', 'comments']),
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Survei sarpras berhasil disubmit.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/v1/evaluations/osis-election
     */
    public function electionInfo(Request $request)
    {
        $studentId = $request->user()->student->id ?? null;
        $election = $this->evaluationService->getActiveElection($studentId);
        if (! $election) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pemilihan OSIS aktif saat ini.',
            ], 404);
        }

        return response()->json($election); // return object directly for Retrofit
    }

    /**
     * POST /api/v1/evaluations/osis-election/vote
     */
    public function castVote(Request $request)
    {
        $request->validate([
            'election_id' => 'required|exists:osis_elections,id',
            'candidate_id' => 'required|exists:osis_candidates,id',
            'biometric_signature' => 'required|string', // Simulated biometric validation
        ]);

        try {
            // Check role: must be student
            $user = $request->user();
            if ($user->role !== 'student' && $user->role !== 'siswa') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya siswa yang berhak memberikan suara.',
                ], 403);
            }

            // Find student model
            $student = $user->student;
            if (! $student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data profil siswa tidak ditemukan.',
                ], 404);
            }

            $this->evaluationService->castVote(
                $request->election_id,
                $request->candidate_id,
                $student->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Suara berhasil direkam secara rahasia dan aman.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
