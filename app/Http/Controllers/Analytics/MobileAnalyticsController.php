<?php

declare(strict_types=1);

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
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
        $request->validate([
            'class' => 'required|string',
            'subject' => 'required|string',
        ]);

        $classroom = Classroom::where('name', $request->query('class'))->first();
        $subject = Subject::where('name', $request->query('subject'))->first();

        if (! $classroom || ! $subject) {
            return response()->json(['message' => 'Kelas atau mata pelajaran tidak ditemukan.'], 404);
        }

        // A teacher may only see performance for a (class, subject) pair
        // they actually teach — checked against real Schedule rows, not
        // trusted from the request alone.
        $user = $request->user();
        $isPrivileged = in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'], true);
        if (! $isPrivileged) {
            $teaches = $this->service->teacherClasses($user);
            $allowed = collect($teaches)->contains(
                fn ($pair) => $pair['className'] === $classroom->name && $pair['subjectName'] === $subject->name
            );
            if (! $allowed) {
                return response()->json(['message' => 'Anda tidak mengajar kelas dan mata pelajaran ini.'], 403);
            }
        }

        return response()->json($this->service->classPerformance($classroom, $subject));
    }

    public function parentChildProgress(Request $request): JsonResponse
    {
        $requestedId = $request->query('student_id');
        $query = Student::where('parent_id', $request->user()->id);
        $student = $requestedId ? $query->where('id', $requestedId)->first() : $query->first();

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
