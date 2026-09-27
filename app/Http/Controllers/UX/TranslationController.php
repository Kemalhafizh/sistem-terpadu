<?php

namespace App\Http\Controllers\UX;

use App\Http\Controllers\Controller;
use App\Models\TranslationOverride;
use App\Services\UX\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index(TranslationService $service)
    {
        $overrides = TranslationOverride::latest()->get();
        $completion = $service->getCompletionPercentage();

        return view('i18n.translation-manager', compact('overrides', 'completion'));
    }

    public function override(Request $request, TranslationService $service)
    {
        $validated = $request->validate([
            'locale' => 'required|in:id,en,ar',
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        $service->overrideTranslation($validated['locale'], $validated['key'], $validated['value'], auth()->id());

        return back()->with('success', "Terjemahan [{$validated['key']}] untuk bahasa ".strtoupper($validated['locale']).' berhasil diperbarui!');
    }

    public function completionReport(TranslationService $service)
    {
        $completion = $service->getCompletionPercentage();

        return view('i18n.completion-report', compact('completion'));
    }
}
