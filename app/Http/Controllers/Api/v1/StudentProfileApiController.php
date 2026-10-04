<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\StudentProfile360Service;
use App\Support\Access\StudentDataAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $ownProfile = in_array($user->role, StudentDataAccess::STUDENT_ROLES, true);
        if (! ($id && is_numeric($id)) && ! $ownProfile) {
            abort(422, 'Parameter id siswa wajib diisi.');
        }
        $targetId = ($id && is_numeric($id)) ? (int) $id : $user->id;

        // Students see themselves, parents their own children, staff anyone.
        StudentDataAccess::authorizeStudentUser($user, $targetId);
        $this->ensureStudentExists($targetId);

        return $targetId;
    }

    /** A user without a student record has no profile; it used to get one named "Siswa" full of zeros. */
    protected function ensureStudentExists(int $userId): void
    {
        if (! $this->service->resolveStudent($userId)) {
            abort(404, __('Data siswa untuk akun ini belum tercatat. Hubungi tata usaha sekolah.'));
        }
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
        $this->ensureStudentExists($request->user()->id);
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
