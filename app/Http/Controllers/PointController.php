<?php

namespace App\Http\Controllers;

use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * Display a listing of students with their total points.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'classroom', 'pointLogs']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('nis', 'like', "%{$search}%");
        }

        $students = $query->paginate(10);
        $categories = PointCategory::where('is_active', true)->get();

        return view('points.index', compact('students', 'categories'));
    }

    /**
     * Display the point history for a specific student.
     */
    public function show(Student $student)
    {
        $student->load(['user', 'classroom', 'pointLogs.creator', 'pointLogs.category']);
        $logs = $student->pointLogs()->latest()->paginate(20);
        $categories = PointCategory::where('is_active', true)->get();

        return view('points.show', compact('student', 'logs', 'categories'));
    }

    /**
     * Store a new point log entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'point_category_id' => 'nullable|exists:point_categories,id',
            'points' => 'required_without:point_category_id|nullable|integer',
            'type' => 'required_without:point_category_id|nullable|in:penalty,reward,adjustment',
            'description' => 'required|string|max:500',
        ]);

        $points = $validated['points'] ?? null;
        $type = $validated['type'] ?? null;

        if ($request->point_category_id) {
            $category = PointCategory::find($request->point_category_id);
            $points = $category->default_points;
            $type = $category->type;
        }

        StudentPointLog::create([
            'student_id' => $validated['student_id'],
            'point_category_id' => $validated['point_category_id'] ?? null,
            'created_by' => auth()->id(),
            'points' => $points,
            'type' => $type,
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Catatan poin berhasil disimpan.');
    }

    /**
     * Delete a point log entry.
     */
    public function destroy(StudentPointLog $log)
    {
        $log->delete();

        return back()->with('success', 'Catatan poin berhasil dihapus.');
    }
}
