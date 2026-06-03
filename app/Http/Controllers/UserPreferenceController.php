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
        if (!in_array($locale, ['id', 'en', 'ar'])) {
            return back()->with('error', 'Bahasa tidak didukung / Language not supported.');
        }

        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $locale]);
        }

        session(['locale' => $locale]);

        return back()->with('success', $locale === 'ar' ? 'تم تغيير اللغة بنجاح.' : ($locale === 'en' ? 'Language changed successfully.' : 'Bahasa berhasil diubah.'));
    }

    /**
     * Set Preferred Theme (Light/Dark/System)
     */
    public function setTheme(Request $request, string $theme)
    {
        if (!in_array($theme, ['light', 'dark', 'system'])) {
            return back()->with('error', 'Tema tidak valid / Invalid theme.');
        }

        if (auth()->check()) {
            auth()->user()->update(['preferred_theme' => $theme]);
        }

        session(['theme' => $theme]);

        return back()->with('success', 'Tema visual berhasil diperbarui.');
    }
}
