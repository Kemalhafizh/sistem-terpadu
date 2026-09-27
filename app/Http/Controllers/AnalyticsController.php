<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Services\AnalyticsService;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    protected $predictiveService;

    public function __construct(AnalyticsService $analyticsService, PredictiveAnalyticsService $predictiveService)
    {
        $this->analyticsService = $analyticsService;
        $this->predictiveService = $predictiveService;
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

    public function attendance(Request $request)
    {
        $attendanceTrend = $this->analyticsService->getAttendanceTrend(12); // Get 12 months

        return view('analytics.attendance', compact('attendanceTrend'));
    }

    public function academic(Request $request)
    {
        $classroomId = $request->input('classroom_id') ? (int) $request->input('classroom_id') : null;
        $grades = $this->analyticsService->getGradeDistribution($classroomId);
        $classrooms = Classroom::orderBy('name')->get();

        return view('analytics.academic', compact('grades', 'classrooms', 'classroomId'));
    }

    public function financial(Request $request)
    {
        $finance = $this->analyticsService->getFinancialOverview();
        $forecast = $this->predictiveService->generateTrendForecast('finance');

        return view('analytics.financial', compact('finance', 'forecast'));
    }

    public function islamic(Request $request)
    {
        $bk = $this->analyticsService->getPointStatistics();

        return view('analytics.islamic', compact('bk'));
    }

    public function studentRisk(Request $request)
    {
        $classroomId = $request->input('classroom_id') ? (int) $request->input('classroom_id') : null;
        $atRiskStudents = $this->predictiveService->identifyAtRiskStudents($classroomId);
        $classrooms = Classroom::orderBy('name')->get();

        return view('analytics.student-risk', compact('atRiskStudents', 'classrooms', 'classroomId'));
    }

    public function comparison(Request $request)
    {
        $spmb = $this->analyticsService->getSpmbComparison();

        return view('analytics.comparison', compact('spmb'));
    }

    public function heatmap(Request $request)
    {
        $days = in_array((int) $request->input('days'), [7, 14, 30], true) ? (int) $request->input('days') : 14;
        $heatmap = $this->analyticsService->getClassroomAbsenceHeatmap($days);

        return view('analytics.heatmap', compact('heatmap', 'days'));
    }

    public function customReport(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();

        // Generates on-the-fly custom reports based on filters
        return view('analytics.custom-report', compact('classrooms'));
    }
}
