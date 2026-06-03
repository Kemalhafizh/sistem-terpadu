<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Student;

class StudentAcademicController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            abort(404, 'Profil siswa tidak ditemukan.');
        }

        $activeYear = AcademicYear::where('is_active', true)->first();

        // Data Rapor (Ledger)
        $reportCard = ReportCard::where('student_id', $student->id)
            ->where('academic_year_id', $activeYear?->id)
            ->first();

        // Nilai Detail per Mapel
        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $activeYear?->id)
            ->get();

        return view('academic.student.index', compact('student', 'reportCard', 'grades', 'activeYear'));
    }
}
