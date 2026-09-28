<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\ParentExperienceService;
use App\Support\Access\StudentDataAccess;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    use ApiResponseTrait;

    protected ParentExperienceService $parentService;

    public function __construct(ParentExperienceService $parentService)
    {
        $this->parentService = $parentService;
    }

    private function authorizeChildAccess(Request $request, ?int $childId = null): int
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        // 1. Superadmin may look at any child, but must say which one.
        if ($user->role === 'superadmin') {
            if ($childId === null) {
                abort(422, 'Parameter child_id wajib diisi.');
            }

            return $childId;
        }

        // 2. Parent / Orangtua: must be registered parent of this child
        if (in_array($user->role, StudentDataAccess::PARENT_ROLES, true)) {
            $studentQuery = Student::where('parent_id', $user->id);
            if ($childId !== null) {
                $child = $studentQuery->where('id', $childId)->first();
            } else {
                $child = $studentQuery->first();
            }

            if (! $child) {
                abort(403, 'Akses ditolak. Anda tidak berwenang mengakses data siswa ini.');
            }

            return (int) $child->id;
        }

        // 3. Student / Siswa: only allowed to view their own profile/feed
        if (in_array($user->role, ['student', 'siswa'], true)) {
            $student = $user->student;
            $ownId = $student ? (int) $student->id : 1;
            if ($childId !== null && (int) $childId !== $ownId) {
                abort(403, 'Akses ditolak. Siswa dilarang menginspeksi aktivitas siswa lain.');
            }

            return $ownId;
        }

        abort(403, 'Akses ditolak. Peran tidak diizinkan mengakses data siswa.');
    }

    /**
     * GET /api/v1/parent/child/{childId}/feed OR /api/v1/parent/feed
     */
    public function activityFeed(Request $request, $childId = null)
    {
        $resolvedId = $this->authorizeChildAccess($request, $childId !== null ? (int) $childId : null);
        $feed = $this->parentService->getChildActivityFeed($resolvedId);

        return $this->successResponse($feed, 'Child activity feed retrieved successfully');
    }

    /**
     * GET /api/v1/parent/child/{childId}/digest OR /api/v1/parent/weekly-digest
     */
    public function weeklyDigest(Request $request, $childId = null)
    {
        $resolvedId = $this->authorizeChildAccess($request, $childId !== null ? (int) $childId : null);
        $digest = $this->parentService->generateWeeklyDigest($resolvedId);

        return $this->successResponse($digest, 'Weekly digest retrieved successfully');
    }

    /**
     * GET /api/v1/parent/child/{childId}/benchmark OR /api/v1/parent/comparison
     */
    public function benchmarkClass(Request $request, $childId = null)
    {
        $resolvedId = $this->authorizeChildAccess($request, $childId !== null ? (int) $childId : null);
        $benchmark = $this->parentService->getChildVsClassAverage($resolvedId);

        return $this->successResponse($benchmark, 'Benchmark class comparison retrieved successfully');
    }

    /**
     * GET /api/v1/parent/actions
     */
    public function quickActions(Request $request)
    {
        $parentId = $request->user() ? $request->user()->id : 1;
        $actions = $this->parentService->getParentQuickActions($parentId);

        return $this->successResponse($actions, 'Parent quick actions retrieved successfully');
    }
}
