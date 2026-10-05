<?php

namespace App\Http\Controllers\Inclusive;

use App\Http\Controllers\Controller;
use App\Models\AccommodationRegistry;
use App\Models\Student;
use App\Support\Access\StudentDataAccess;
use Illuminate\Http\Request;

class AccommodationRegistryController extends Controller
{
    public function index()
    {
        $registries = AccommodationRegistry::with('student')->whereHas('student', fn ($q) => $q->visibleTo(request()->user()))->get();

        return view('fase58.accommodations.registry', compact('registries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'accommodation_category' => 'required|string',
            'specific_need' => 'required|string',
            'is_active' => 'boolean',
            'applicable_contexts' => 'required|array',
            'provision_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        AccommodationRegistry::create($validated);

        return redirect()->back()->with('success', 'Data akomodasi berhasil ditambahkan.');
    }

    public function update(Request $request, AccommodationRegistry $accommodation)
    {
        StudentDataAccess::authorizeStudentRecord($request->user(), $accommodation->student);

        $validated = $request->validate([
            'accommodation_category' => 'required|string',
            'specific_need' => 'required|string',
            'is_active' => 'boolean',
            'applicable_contexts' => 'required|array',
            'provision_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $accommodation->update($validated);

        return redirect()->back()->with('success', 'Data akomodasi berhasil diperbarui.');
    }

    public function destroy(AccommodationRegistry $accommodation)
    {
        StudentDataAccess::authorizeStudentRecord(request()->user(), $accommodation->student);

        $accommodation->delete();

        return redirect()->back()->with('success', 'Data akomodasi berhasil dihapus.');
    }

    public function studentProfile(Student $student)
    {
        StudentDataAccess::authorizeStudentRecord(request()->user(), $student);

        $accommodations = AccommodationRegistry::where('student_id', $student->id)->get();

        return view('fase58.accommodations.student-profile', compact('student', 'accommodations'));
    }
}
