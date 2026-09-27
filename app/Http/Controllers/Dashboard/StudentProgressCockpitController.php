<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Dashboard\StudentCockpitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentProgressCockpitController extends Controller
{
    public function __construct(
        protected StudentCockpitService $cockpitService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $students = Student::take(30)->get();
        $selectedStudent = $students->first();
        $snapshot = $selectedStudent ? $this->cockpitService->buildLearningJourney($selectedStudent) : null;

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'snapshot' => $snapshot]);
        }

        return view('advanced-dashboards.student-progress-cockpit', compact('students', 'selectedStudent', 'snapshot'));
    }

    public function showStudent(Student $student): JsonResponse
    {
        $snapshot = $this->cockpitService->buildLearningJourney($student);

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->nama_lengkap ?? $student->name,
                'nis' => $student->nis ?? '-',
            ],
            'snapshot' => $snapshot,
        ]);
    }
}
