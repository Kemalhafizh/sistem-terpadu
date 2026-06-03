<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        if ($request->has('clear_cache')) {
            $this->analyticsService->clearCache();
        }

        $classroomId = $request->input('classroom_id') ? (int) $request->input('classroom_id') : null;

        $attendanceTrend = $this->analyticsService->getAttendanceTrend();
        $grades = $this->analyticsService->getGradeDistribution($classroomId);
        $finance = $this->analyticsService->getFinancialOverview();
        $bk = $this->analyticsService->getPointStatistics();
        $elearning = $this->analyticsService->getElearningMetrics();
        $spmb = $this->analyticsService->getSpmbComparison();
        $teachers = $this->analyticsService->getTeacherPerformanceMetrics();

        $classrooms = Classroom::orderBy('name')->get();

        return view('analytics.index', compact(
            'attendanceTrend',
            'grades',
            'finance',
            'bk',
            'elearning',
            'spmb',
            'teachers',
            'classrooms',
            'classroomId'
        ));
    }
}
