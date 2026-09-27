<?php

namespace App\Http\Controllers\Inclusive;

use App\Http\Controllers\Controller;
use App\Models\IepDocument;
use Illuminate\Http\Request;

class IepController extends Controller
{
    public function index()
    {
        $ieps = IepDocument::with('student', 'caseManager')->get();

        return view('fase58.iep.list', compact('ieps'));
    }

    public function create()
    {
        return view('fase58.iep.builder');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'case_manager_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'review_date' => 'required|date',
            'present_level_of_performance' => 'required|string',
            'annual_goals' => 'required|array',
            'required_accommodations' => 'required|array',
            'status' => 'required|in:draft,active,reviewed,closed',
        ]);

        IepDocument::create($validated);

        return redirect()->route('iep.index')->with('success', 'Dokumen IEP berhasil dibuat.');
    }

    public function show(IepDocument $iep)
    {
        return view('fase58.iep.progress', compact('iep'));
    }

    public function edit(IepDocument $iep)
    {
        return view('fase58.iep.builder', compact('iep'));
    }

    public function update(Request $request, IepDocument $iep)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'review_date' => 'required|date',
            'present_level_of_performance' => 'required|string',
            'annual_goals' => 'required|array',
            'required_accommodations' => 'required|array',
            'status' => 'required|in:draft,active,reviewed,closed',
        ]);

        $iep->update($validated);

        return redirect()->route('iep.index')->with('success', 'Dokumen IEP berhasil diperbarui.');
    }

    public function destroy(IepDocument $iep)
    {
        $iep->delete();

        return redirect()->route('iep.index')->with('success', 'Dokumen IEP berhasil dihapus.');
    }

    public function progress(IepDocument $iep)
    {
        return view('fase58.iep.progress', compact('iep'));
    }
}
