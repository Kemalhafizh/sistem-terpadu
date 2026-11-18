<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\DataVisualizationService;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class VizController extends Controller
{
    use ApiResponseTrait;

    protected DataVisualizationService $vizService;

    public function __construct(DataVisualizationService $vizService)
    {
        $this->vizService = $vizService;
    }

    /**
     * GET /api/v1/viz/heatmap/attendance
     */
    public function attendanceHeatmap()
    {
        $heatmap = $this->vizService->getAttendanceHeatmap();

        return $this->successResponse($heatmap, 'Attendance heatmap retrieved successfully');
    }

    /**
     * GET /api/v1/viz/distribution/grades/{examId}
     */
    public function gradeDistribution(int $examId)
    {
        $dist = $this->vizService->getGradeDistribution($examId);

        return $this->successResponse($dist, 'Grade distribution statistics retrieved successfully');
    }

    private function authorizeStudentAccess(Request $request, int $studentId): void
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $roles = $user->effectiveRoles();

        // 1. Staff / Teacher / Counselor / Leadership have full pedagogical monitoring access
        if (array_intersect($roles, [
            'admin', 'superadmin', 'bk', 'wali_kelas',
            'waka_kurikulum', 'waka_kesiswaan',
            ...StudentDataAccess::TEACHER_ROLES,
            ...Roles::PRINCIPAL,
        ]) !== []) {
            return;
        }

        // 2. Parent: must be parent of this student
        if (array_intersect($roles, StudentDataAccess::PARENT_ROLES) !== []) {
            $isChild = Student::where('parent_id', $user->id)->where('id', $studentId)->exists();
            if (! $isChild) {
                abort(403, 'Akses ditolak. Anda bukan orang tua yang berwenang untuk siswa ini.');
            }

            return;
        }

        // 3. Student: can only inspect their own metrics
        if (array_intersect($roles, StudentDataAccess::STUDENT_ROLES) !== []) {
            $ownStudent = $user->student;
            if (! $ownStudent || (int) $studentId !== (int) $ownStudent->id) {
                abort(403, 'Akses ditolak. Siswa dilarang melihat visualisasi radar/trend siswa lain.');
            }

            return;
        }

        abort(403, 'Akses ditolak. Peran Anda tidak memiliki hak akses data visualisasi ini.');
    }

    /**
     * GET /api/v1/viz/radar/student/{studentId}
     */
    public function studentRadar(Request $request, int $studentId)
    {
        $this->authorizeStudentAccess($request, $studentId);
        $radar = $this->vizService->getStudentRadarChart($studentId);

        return $this->successResponse($radar, 'Student radar chart data retrieved successfully');
    }

    /**
     * GET /api/v1/viz/trend/student/{studentId}
     */
    public function studentTrend(Request $request, int $studentId)
    {
        $this->authorizeStudentAccess($request, $studentId);
        $trend = $this->vizService->getValueTrend($studentId);

        return $this->successResponse($trend, 'Student value trend data retrieved successfully');
    }

    /**
     * GET /api/v1/viz/comparison/classes
     */
    public function classComparison()
    {
        $comparison = $this->vizService->getClassComparison();

        return $this->successResponse($comparison, 'Class benchmark comparisons retrieved successfully');
    }

    /**
     * GET /api/v1/viz/counters/live
     */
    public function liveCounters()
    {
        $counters = $this->vizService->getLiveCounters();

        return $this->successResponse($counters, 'Live operational counters retrieved successfully');
    }
}
