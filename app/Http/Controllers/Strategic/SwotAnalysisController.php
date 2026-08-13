<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\SwotAnalysis;
use Illuminate\Http\Request;

class SwotAnalysisController extends Controller
{
    public function index()
    {
        $analyses = SwotAnalysis::with('items')->latest()->get();
        return view('strategic.swot.index', compact('analyses'));
    }

    public function create()
    {
        return view('strategic.swot.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'analysis_year' => 'required|integer',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;
        SwotAnalysis::create($validated);

        return redirect()->route('strategic.swot.index')->with('success', 'Analisis SWOT berhasil dibuat.');
    }

    public function show($id)
    {
        $swot = SwotAnalysis::with('items')->findOrFail($id);
        return view('strategic.swot.show', compact('swot'));
    }
}
