<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\CounselingMobileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CounselingMobileApiController extends Controller
{
    protected CounselingMobileService $service;

    public function __construct(CounselingMobileService $service)
    {
        $this->service = $service;
    }

    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->service->getCounselorDashboard($request->user()->id);
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function storeSession(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'category' => 'required|string',
            'notes' => 'nullable|string',
            'action_plan' => 'nullable|string',
            'is_confidential' => 'nullable|boolean',
        ]);

        $session = $this->service->createSession($request->all(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Sesi konseling berhasil direkam',
            'data' => $session,
        ], 201);
    }

    public function requestAppointment(Request $request): JsonResponse
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'preferred_date' => 'nullable|date',
            'category' => 'nullable|string',
        ]);

        $appointment = $this->service->requestAppointment($request->user()->id, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Permintaan janji temu konseling BK berhasil diajukan',
            'data' => $appointment,
        ], 201);
    }

    public function studentAppointments(Request $request): JsonResponse
    {
        $appointments = $this->service->getStudentAppointments($request->user()->id);
        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    public function atRiskStudents(Request $request): JsonResponse
    {
        $atRisk = $this->service->getAtRiskStudents();
        return response()->json([
            'success' => true,
            'data' => $atRisk,
        ]);
    }

    public function addFollowUp(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'notes' => 'required|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $session = $this->service->addFollowUp($id, $request->notes, $request->follow_up_date);

        return response()->json([
            'success' => true,
            'message' => 'Catatan tindak lanjut berhasil ditambahkan',
            'data' => $session,
        ]);
    }
}
