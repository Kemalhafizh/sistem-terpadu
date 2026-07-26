<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicPathway;
use App\Models\User;
use App\Services\Academic\AcademicPathwayService;
use Illuminate\Http\Request;

class AcademicPathwayController extends Controller
{
    public function pathways()
    {
        $pathways = AcademicPathway::withCount('enrollments')->get();
        return view('pathway.index', compact('pathways'));
    }

    public function enrollForm()
    {
        $students = User::where('role', 'student')->get();
        $pathways = AcademicPathway::where('is_active', true)->get();

        return view('pathway.index', compact('students', 'pathways'));
    }

    public function storeEnrollment(Request $request, AcademicPathwayService $service)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'academic_pathway_id' => 'required|exists:academic_pathways,id',
        ]);

        $service->enrollStudent($validated['student_id'], $validated['academic_pathway_id']);

        return back()->with('success', 'Peminatan Akademik siswa berhasil didaftarkan!');
    }

    public function progressDashboard(int $studentId, AcademicPathwayService $service)
    {
        $student = User::where('role', 'student')->findOrFail($studentId);
        $readiness = $service->checkGraduationReadiness($studentId);

        return view('pathway.progress-dashboard', compact('student', 'readiness'));
    }

    public function graduationReadiness(AcademicPathwayService $service)
    {
        $studentId = auth()->id();
        $readiness = $service->checkGraduationReadiness($studentId);

        return view('pathway.graduation-readiness', compact('readiness'));
    }
}
