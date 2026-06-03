<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::latest()->paginate(10);

        return view('academic-years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('academic-years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|string|max:20', // e.g. 2024/2025
            'semester' => 'required|in:Ganjil,Genap',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        AcademicYear::create($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'year' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $academicYear->update($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'Tidak bisa menghapus periode yang sedang aktif.');
        }

        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * Activate the specified academic year.
     */
    public function activate(AcademicYear $academicYear)
    {
        $academicYear->activate();

        return redirect()->route('academic-years.index')
            ->with('success', "Tahun ajaran {$academicYear->year} ({$academicYear->semester}) sekarang aktif.");
    }
}
