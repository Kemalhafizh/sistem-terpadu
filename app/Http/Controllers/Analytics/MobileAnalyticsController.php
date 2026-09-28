<?php

declare(strict_types=1);

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Analytics\MobileAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileAnalyticsController extends Controller
{
    public function __construct(
        protected MobileAnalyticsService $service
    ) {}

    public function studentSummary(Request $request): JsonResponse
    {
        $student = Student::where('user_id', $request->user()->id)->first();
        if (! $student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan untuk akun ini.'], 404);
        }

        return response()->json($this->service->studentSummary($student));
    }

    public function teacherClasses(Request $request): JsonResponse
    {
        return response()->json($this->service->teacherClasses($request->user()));
    }

    public function classPerformance(Request $request): JsonResponse
    {
        // Ids from teacher/my-classes are exact; a name alone is ambiguous
        // because every academic year has its own "X-1".
        $request->validate([
            'classroom_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'class' => 'required_without:classroom_id|nullable|string',
            'subject' => 'required_without:subject_id|nullable|string',
        ]);

        $classroom = $request->filled('classroom_id')
            ? Classroom::find($request->integer('classroom_id'))
            : $this->classroomNamed((string) $request->query('class'));
        $subject = $request->filled('subject_id')
            ? Subject::find($request->integer('subject_id'))
            : Subject::where('name', $request->query('subject'))->first();

        if (! $classroom || ! $subject) {
            return response()->json(['message' => 'Kelas atau mata pelajaran tidak ditemukan.'], 404);
        }

        // A teacher may only see a (class, subject) pair they teach this
        // year, never one trusted from the request alone.
        $user = $request->user();
        $isPrivileged = in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'], true);
        if (! $isPrivileged) {
            $allowed = collect($this->service->teacherClasses($user))->contains(
                fn ($pair) => $pair['classroomId'] === $classroom->id && $pair['subjectId'] === $subject->id
            );
            if (! $allowed) {
                return response()->json(['message' => 'Anda tidak mengajar kelas dan mata pelajaran ini.'], 403);
            }
        }

        return response()->json($this->service->classPerformance($classroom, $subject));
    }

    /** The class of that name in the active academic year, else the newest one. */
    private function classroomNamed(string $name): ?Classroom
    {
        $year = AcademicYear::current()?->year;

        return Classroom::where('name', $name)
            ->when($year, fn ($q) => $q->orderByRaw('case when academic_year = ? then 0 else 1 end', [$year]))
            ->orderByDesc('id')
            ->first();
    }

    public function parentChildProgress(Request $request): JsonResponse
    {
        // student_id is students.id or the uuid from parent/children; none = the first child.
        $requested = (string) $request->query('student_id', '');
        $query = Student::where('parent_id', $request->user()->id);
        $student = match (true) {
            $requested === '' => $query->first(),
            ctype_digit($requested) => $query->where('id', (int) $requested)->first(),
            default => $query->where('uuid', $requested)->first(),
        };

        if (! $student) {
            return response()->json(['message' => 'Data anak tidak ditemukan untuk akun ini.'], 404);
        }

        return response()->json($this->service->parentChildProgress($student));
    }

    public function executiveKpi(): JsonResponse
    {
        return response()->json($this->service->executiveKpi());
    }
}
