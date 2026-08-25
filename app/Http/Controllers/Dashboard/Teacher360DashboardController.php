<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Teacher360Metric;
use App\Models\User;
use App\Services\Dashboard\TeacherPerformance360Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Teacher360DashboardController extends Controller
{
    public function __construct(
        protected TeacherPerformance360Service $performanceService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $teachers = User::where('role', 'guru')->get();
        $selectedTeacher = $teachers->first() ?? $request->user();
        $metric = $selectedTeacher ? $this->performanceService->aggregateTeacherScores($selectedTeacher) : null;

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'metric' => $metric]);
        }

        return view('advanced-dashboards.teacher-360-dashboard', compact('teachers', 'selectedTeacher', 'metric'));
    }

    public function showTeacher(User $teacher): JsonResponse
    {
        $metric = $this->performanceService->aggregateTeacherScores($teacher);

        return response()->json([
            'success' => true,
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
            ],
            'metric' => $metric,
        ]);
    }
}
