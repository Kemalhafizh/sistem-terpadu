<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\StudentProfile360Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentProfileApiController extends Controller
{
    protected StudentProfile360Service $service;

    public function __construct(StudentProfile360Service $service)
    {
        $this->service = $service;
    }

    public function show(Request $request, $id = null): JsonResponse
    {
        $targetId = ($id && is_numeric($id)) ? (int) $id : $request->user()->id;
        $profile = $this->service->getComprehensiveProfile($targetId);

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    public function myProfile(Request $request): JsonResponse
    {
        $profile = $this->service->getComprehensiveProfile($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    public function academic(Request $request, $id = null): JsonResponse
    {
        $targetId = ($id && is_numeric($id)) ? (int) $id : $request->user()->id;
        $data = $this->service->getAcademicSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function ibadah(Request $request, $id = null): JsonResponse
    {
        $targetId = ($id && is_numeric($id)) ? (int) $id : $request->user()->id;
        $data = $this->service->getIbadahSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function discipline(Request $request, $id = null): JsonResponse
    {
        $targetId = ($id && is_numeric($id)) ? (int) $id : $request->user()->id;
        $data = $this->service->getDisciplineSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function health(Request $request, $id = null): JsonResponse
    {
        $targetId = ($id && is_numeric($id)) ? (int) $id : $request->user()->id;
        $data = $this->service->getHealthSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
