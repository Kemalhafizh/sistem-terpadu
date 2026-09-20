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

    protected function resolveAuthorizedStudentId(Request $request, $id): int
    {
        $user = $request->user();
        $targetId = ($id && is_numeric($id)) ? (int) $id : $user->id;

        // If logged in as student, they can ONLY view their own profile
        if (in_array($user->role, ['student', 'siswa'], true)) {
            if ($targetId !== $user->id) {
                abort(403, 'Akses ditolak. Anda hanya berhak mengakses profil Anda sendiri.');
            }
        }

        // If logged in as parent, they can only view their own children
        if ($user->role === 'parent') {
            $childUserIds = $user->children ? $user->children->pluck('user_id')->filter()->toArray() : [];
            if ($targetId !== $user->id && !in_array($targetId, $childUserIds, true)) {
                abort(403, 'Akses ditolak. Anda hanya berhak mengakses profil anak Anda.');
            }
        }

        return $targetId;
    }

    public function show(Request $request, $id = null): JsonResponse
    {
        $targetId = $this->resolveAuthorizedStudentId($request, $id);
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
        $targetId = $this->resolveAuthorizedStudentId($request, $id);
        $data = $this->service->getAcademicSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function ibadah(Request $request, $id = null): JsonResponse
    {
        $targetId = $this->resolveAuthorizedStudentId($request, $id);
        $data = $this->service->getIbadahSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function discipline(Request $request, $id = null): JsonResponse
    {
        $targetId = $this->resolveAuthorizedStudentId($request, $id);
        $data = $this->service->getDisciplineSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function health(Request $request, $id = null): JsonResponse
    {
        $targetId = $this->resolveAuthorizedStudentId($request, $id);
        $data = $this->service->getHealthSummary($targetId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
