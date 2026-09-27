<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\SatisfactionSurvey;
use Illuminate\Http\Request;

class SatisfactionSurveyController extends Controller
{
    public function index()
    {
        $surveys = SatisfactionSurvey::latest()->get();

        return view('strategic.survey.index', compact('surveys'));
    }

    public function create()
    {
        return view('strategic.survey.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'survey_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;
        SatisfactionSurvey::create($validated);

        return redirect()->route('strategic.survey.index')->with('success', 'Survei Kepuasan berhasil dibuat.');
    }

    public function show($id)
    {
        $survey = SatisfactionSurvey::with('dimensions.questions')->findOrFail($id);

        return view('strategic.survey.show', compact('survey'));
    }

    public function respond($id)
    {
        $survey = SatisfactionSurvey::with('dimensions.questions')->findOrFail($id);

        return view('strategic.survey.respond', compact('survey'));
    }

    public function storeResponse(Request $request, $id)
    {
        return redirect()->route('strategic.survey.index')->with('success', 'Tanggapan survei berhasil disimpan.');
    }

    public function results($id)
    {
        $survey = SatisfactionSurvey::with('dimensions.questions.responses')->findOrFail($id);

        return view('strategic.survey.results', compact('survey'));
    }
}
