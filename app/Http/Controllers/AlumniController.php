<?php

namespace App\Http\Controllers;

use App\Models\AlumniData;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = AlumniData::with(['student.user', 'student.classroom']);

        if ($request->year) {
            $query->where('graduation_year', $request->year);
        }

        $alumni = $query->latest()->paginate(20);

        return view('students.alumni.index', compact('alumni'));
    }

    public function create()
    {
        $activeStudents = Student::where('status', 'active')->with('user', 'classroom')->get();

        return view('students.alumni.create', compact('activeStudents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'graduation_year' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'current_activity' => 'required|in:study,work,entrepreneur,unemployed,other',
            'institution_name' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            AlumniData::create($validated);
            Student::find($validated['student_id'])->update(['status' => 'alumni']);
        });

        return redirect()->route('alumni.index')->with('success', 'Data alumni berhasil ditambahkan.');
    }
}
