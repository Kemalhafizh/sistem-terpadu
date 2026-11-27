<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Academic\P5ProjectService;
use App\Support\Access\StudentDataAccess;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class P5ReportApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly P5ProjectService $p5Service,
    ) {}

    /**
     * Retrieve Rapor Projek P5 (Kurikulum Merdeka) for the authenticated student,
     * a parent's child (`?student_uuid=`), or an authorized teacher/leadership user.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentUuid = trim((string) $request->query('student_uuid', ''));
        $schoolYearId = $request->filled('school_year_id') ? (int) $request->integer('school_year_id') : null;

        if ($studentUuid !== '') {
            $student = Student::where('uuid', $studentUuid)->first();
        } elseif ($user->hasRole(StudentDataAccess::STUDENT_ROLES)) {
            $student = Student::where('user_id', $user->id)->first();
        } elseif ($user->hasRole(StudentDataAccess::PARENT_ROLES)) {
            $student = Student::where('parent_id', $user->id)->orderBy('id')->first();
        } else {
            $student = null;
        }

        if (! $student) {
            return $this->errorResponse(__('Data siswa tidak ditemukan.'), 404);
        }

        if (! $this->p5Service->canViewStudentReport($user, $student)) {
            return $this->errorResponse(__('Anda tidak berwenang mengakses Rapor Projek P5 siswa ini.'), 403);
        }

        $report = $this->p5Service->buildStudentReport($student, $schoolYearId);

        return $this->successResponse([
            'student' => [
                'uuid' => $student->uuid,
                'name' => $student->user?->name,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'classroom' => $report['classroom']?->name,
                'grade_level' => $report['classroom']?->grade_level,
            ],
            'school_year' => $report['school_year'] ? [
                'id' => $report['school_year']->id,
                'name' => $report['school_year']->name,
            ] : null,
            'phase' => $report['phase'],
            'predicates_legend' => $report['predicates_legend'],
            'projects' => $report['projects'],
        ], __('Rapor Projek P5 berhasil diambil.'));
    }
}
