<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\DataVisualizationService;
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
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // 1. Staff / Teacher / Counselor / Admin have full pedagogical monitoring access
        if (in_array($user->role, ['admin', 'superadmin', 'guru', 'teacher', 'bk', 'kepala_sekolah', 'kepsek'], true)) {
            return;
        }

        // 2. Parent: must be parent of this student
        if (in_array($user->role, ['parent', 'orangtua'], true)) {
            $isChild = \App\Models\Student::where('parent_id', $user->id)->where('id', $studentId)->exists();
            if (!$isChild) {
                abort(403, 'Akses ditolak. Anda bukan orang tua yang berwenang untuk siswa ini.');
            }
            return;
        }

        // 3. Student: can only inspect their own metrics
        if (in_array($user->role, ['student', 'siswa'], true)) {
            $ownStudent = $user->student;
            $ownId = $ownStudent ? (int) $ownStudent->id : 1;
            if ((int) $studentId !== $ownId) {
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
