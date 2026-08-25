<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accessibility;

use App\Http\Controllers\Controller;
use App\Services\Accessibility\UniversalDesignThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemePreferenceController extends Controller
{
    public function __construct(
        protected UniversalDesignThemeService $themeService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $preferences = $this->themeService->getPreferences($request->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'preferences' => $preferences]);
        }

        return view('accessibility.accessibility-settings-panel', compact('preferences'));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'color_mode' => 'required|in:light,dark,system,high_contrast',
            'font_scaling' => 'required|in:90%,100%,115%,130%',
            'reduce_animations' => 'required|boolean',
            'dyslexic_font_enabled' => 'required|boolean',
            'primary_color_hex' => 'nullable|string|max:10',
        ]);

        $updated = $this->themeService->updatePreferences($request->user(), $validated);

        return response()->json([
            'success' => true,
            'message' => 'Preferensi aksesibilitas dan tema berhasil disimpan.',
            'preferences' => $updated,
        ]);
    }
}
