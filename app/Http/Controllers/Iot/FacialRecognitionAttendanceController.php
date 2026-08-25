<?php

declare(strict_types=1);

namespace App\Http\Controllers\Iot;

use App\Http\Controllers\Controller;
use App\Services\Iot\FacialRecognitionAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacialRecognitionAttendanceController extends Controller
{
    public function __construct(
        protected FacialRecognitionAttendanceService $facialService
    ) {}

    public function enroll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'embedding_vector' => 'required|array|size:512',
        ]);

        $profile = $this->facialService->enrollFace($request->user(), $validated['embedding_vector']);

        return response()->json([
            'success' => true,
            'message' => 'Profil biometrik wajah berhasil didaftarkan.',
            'profile_id' => $profile->id,
        ], 200);
    }

    public function matchGate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gate_camera_id' => 'required|string',
            'gate_location' => 'required|string',
            'captured_vector' => 'required|array|size:512',
            'liveness_verified' => 'nullable|boolean',
        ]);

        $result = $this->facialService->matchAndLogAttendance(
            $validated['gate_camera_id'],
            $validated['gate_location'],
            $validated['captured_vector'],
            (bool) ($validated['liveness_verified'] ?? true)
        );

        $status = $result['success'] ? 200 : 422;

        return response()->json($result, $status);
    }
}
