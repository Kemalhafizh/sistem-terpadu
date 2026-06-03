<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppearanceService;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    protected $appearanceService;

    public function __construct(AppearanceService $appearanceService)
    {
        $this->appearanceService = $appearanceService;
    }

    /**
     * Show the appearance settings visual editor.
     */
    public function index()
    {
        $appearance = $this->appearanceService->getSettings();
        $fonts = $this->appearanceService->getAvailableFonts();

        return view('admin.appearance.index', compact('appearance', 'fonts'));
    }

    /**
     * Update the school appearance settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'accent_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sidebar_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'font_family' => 'required|string|max:100',
            'custom_css' => 'nullable|string',
            'footer_text' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'logo_dark' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|file|mimes:png,ico,jpg,jpeg|max:1024',
            'login_background' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
        ], [
            'primary_color.regex' => 'Format kode hex warna utama tidak valid (contoh: #3b82f6).',
            'secondary_color.regex' => 'Format kode hex warna sekunder tidak valid.',
            'accent_color.regex' => 'Format kode hex warna aksen tidak valid.',
            'sidebar_color.regex' => 'Format kode hex warna sidebar tidak valid.',
        ]);

        $data = $request->all();

        // Pass files to data array
        $files = ['logo', 'logo_dark', 'favicon', 'login_background'];
        foreach ($files as $f) {
            if ($request->hasFile($f)) {
                $data[$f] = $request->file($f);
            }
        }

        $this->appearanceService->saveSettings($data);

        return redirect()->route('admin.appearance.index')->with('success', 'Pengaturan tampilan sekolah berhasil diperbarui.');
    }

    /**
     * Reset appearance settings to factory defaults.
     */
    public function reset()
    {
        $this->appearanceService->resetToDefault();

        return redirect()->route('admin.appearance.index')->with('success', 'Tampilan sekolah berhasil dikembalikan ke pengaturan awal.');
    }
}
