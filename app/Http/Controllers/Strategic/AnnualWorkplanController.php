<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\AnnualWorkplan;
use Illuminate\Http\Request;

class AnnualWorkplanController extends Controller
{
    public function index()
    {
        $workplans = AnnualWorkplan::with('programs')->latest()->get();

        return view('strategic.workplan.index', compact('workplans'));
    }

    public function create()
    {
        return view('strategic.workplan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'fiscal_year' => 'required|integer',
            'total_budget' => 'required|numeric',
        ]);

        $validated['prepared_by'] = auth()->id() ?? 1;
        AnnualWorkplan::create($validated);

        return redirect()->route('strategic.workplan.index')->with('success', 'RKS berhasil dibuat.');
    }

    public function show($id)
    {
        $workplan = AnnualWorkplan::with('programs.activities')->findOrFail($id);

        return view('strategic.workplan.show', compact('workplan'));
    }
}
