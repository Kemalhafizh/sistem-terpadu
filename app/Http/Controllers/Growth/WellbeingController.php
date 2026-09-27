<?php

namespace App\Http\Controllers\Growth;

use App\Http\Controllers\Controller;
use App\Models\WellbeingJournal;
use App\Models\WellbeingScreening;
use App\Services\Growth\WellbeingService;
use Illuminate\Http\Request;

class WellbeingController extends Controller
{
    public function screenings()
    {
        $screenings = WellbeingScreening::withCount('responses')->latest()->get();

        return view('wellbeing.screenings', compact('screenings'));
    }

    public function createScreening()
    {
        return view('wellbeing.fill-screening'); // Form view
    }

    public function storeScreening(Request $request, WellbeingService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'screening_type' => 'required|in:srq20,phq9,custom',
            'academic_year' => 'required|string|max:9',
            'semester' => 'required|in:odd,even',
            'target_grade' => 'nullable|string',
        ]);

        $screening = $service->createScreening($validated, auth()->id());
        $service->activateScreening($screening);

        return redirect()->route('growth.wellbeing.screenings')
            ->with('success', "Instrumen Skrining Mental [{$screening->title}] berhasil dibuat dan diaktifkan!");
    }

    public function fillScreening(int $id)
    {
        $screening = WellbeingScreening::findOrFail($id);

        return view('wellbeing.fill-screening', compact('screening'));
    }

    public function submitScreening(int $id, Request $request, WellbeingService $service)
    {
        $screening = WellbeingScreening::findOrFail($id);

        $validated = $request->validate([
            'responses' => 'required|array',
        ]);

        $studentId = auth()->id();
        $responseObj = $service->submitResponse($screening, $studentId, $validated);

        return redirect()->route('growth.wellbeing.screenings')
            ->with('success', 'Skrining kesehatan mental berhasil dikirim! Tingkat Risiko: '.strtoupper($responseObj->risk_level));
    }

    public function results(WellbeingService $service)
    {
        $heatmap = $service->getCounselorHeatmap();

        return view('wellbeing.results', compact('heatmap'));
    }

    public function moodJournal(Request $request, WellbeingService $service)
    {
        $studentId = auth()->id();
        $journals = WellbeingJournal::where('student_id', $studentId)->latest('created_at')->get();

        return view('wellbeing.mood-journal', compact('journals'));
    }

    public function storeMoodJournal(Request $request, WellbeingService $service)
    {
        $validated = $request->validate([
            'mood_level' => 'required|integer|between:1,5',
            'journal_entry' => 'nullable|string',
            'sleep_hours' => 'nullable|numeric|between:0,24',
        ]);

        $service->addMoodJournalEntry(auth()->id(), $validated);

        return back()->with('success', 'Private Mood Journal hari ini berhasil dicatat!');
    }
}
