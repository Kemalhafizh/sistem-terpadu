<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::with('teacher')->paginate(10);

        return view('classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        $teachers = User::where('role', 'guru')->get();

        return view('classrooms.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|in:X,XI,XII',
            'major' => 'required|string|max:100',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'required|string|max:20',
        ]);

        Classroom::create($validated);

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $classroom = Classroom::findOrFail($id);
        $teachers = User::where('role', 'guru')->get();

        return view('classrooms.edit', compact('classroom', 'teachers'));
    }

    public function update(Request $request, string $id)
    {
        $classroom = Classroom::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|in:X,XI,XII',
            'major' => 'required|string|max:100',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'required|string|max:20',
        ]);

        $classroom->update($validated);

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        Classroom::findOrFail($id)->delete();

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil dihapus');
    }
}
