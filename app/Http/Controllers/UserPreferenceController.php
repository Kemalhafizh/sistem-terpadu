<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    /**
     * Set Preferred Locale (Language)
     */
    public function setLocale(Request $request, string $locale)
    {
        if (! in_array($locale, ['id', 'en', 'ar'])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Language not supported'], 400);
            }

            return back()->with('error', 'Bahasa tidak didukung / Language not supported.');
        }

        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $locale]);
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        $message = match ($locale) {
            'ar' => 'تم تغيير اللغة بنجاح.',
            'en' => 'Language changed successfully.',
            default => 'Bahasa berhasil diubah.',
        };

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'locale' => $locale, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Set Preferred Theme (Light/Dark/System)
     */
    public function setTheme(Request $request, string $theme)
    {
        if (! in_array($theme, ['light', 'dark', 'system'])) {
            return back()->with('error', 'Tema tidak valid / Invalid theme.');
        }

        if (auth()->check()) {
            auth()->user()->update(['preferred_theme' => $theme]);
        }

        session(['theme' => $theme]);

        return back()->with('success', 'Tema visual berhasil diperbarui.');
    }
}
