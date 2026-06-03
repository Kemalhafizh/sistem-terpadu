<?php

namespace App\Services;

use App\Models\AppearanceSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class AppearanceService
{
    /**
     * Get the active appearance settings, or instantiate with defaults.
     */
    public function getSettings(): AppearanceSetting
    {
        $settings = AppearanceSetting::first();

        if (!$settings) {
            $settings = AppearanceSetting::create([
                'app_name' => 'Sistem Terpadu SMA ISA 1',
                'logo_path' => null,
                'logo_dark_path' => null,
                'favicon_path' => null,
                'primary_color' => '#3b82f6',
                'secondary_color' => '#8b5cf6',
                'accent_color' => '#06b6d4',
                'sidebar_color' => '#0f172a',
                'font_family' => 'Inter',
                'custom_css' => null,
                'login_background_path' => null,
                'footer_text' => '© 2026 SMA Islam Sultan Agung 1 Semarang',
            ]);
        }

        return $settings;
    }

    /**
     * Get the list of premium Google Fonts available for selection.
     */
    public function getAvailableFonts(): array
    {
        return [
            'Inter' => 'Inter (Modern & Clean)',
            'Poppins' => 'Poppins (Soft & Friendly)',
            'Roboto' => 'Roboto (Sharp & Standard)',
            'Outfit' => 'Outfit (Sophisticated & Modern)',
            'Plus Jakarta Sans' => 'Plus Jakarta Sans (Sleek & Professional)',
            'Montserrat' => 'Montserrat (Bold & Elegant)',
            'Playfair Display' => 'Playfair Display (Classic Serif)',
        ];
    }

    /**
     * Generate dynamic CSS variable injection based on setting state.
     */
    public function generateCssVariables(AppearanceSetting $setting): string
    {
        // Fallback checks
        $primary = $setting->primary_color ?: '#3b82f6';
        $secondary = $setting->secondary_color ?: '#8b5cf6';
        $accent = $setting->accent_color ?: '#06b6d4';
        $sidebar = $setting->sidebar_color ?: '#0f172a';
        $font = $setting->font_family ?: 'Inter';

        $css = "
        :root {
            --color-primary: {$primary};
            --color-secondary: {$secondary};
            --color-accent: {$accent};
            --color-sidebar: {$sidebar};
            --font-family: '{$font}', sans-serif;
        }
        ";

        if ($setting->custom_css) {
            $css .= "\n/* Kustom CSS Tambahan */\n" . $setting->custom_css;
        }

        return $css;
    }

    /**
     * Save/Update the appearance settings with optional image file uploads.
     */
    public function saveSettings(array $data): void
    {
        $settings = $this->getSettings();
        $updateData = [];

        // Handle text/config fields
        $fields = [
            'app_name', 'primary_color', 'secondary_color', 
            'accent_color', 'sidebar_color', 'font_family', 
            'custom_css', 'footer_text'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        // Handle Logo Uploads
        $uploadFields = ['logo', 'logo_dark', 'favicon', 'login_background'];
        foreach ($uploadFields as $uf) {
            $key = $uf . '_path';
            if (isset($data[$uf]) && $data[$uf] instanceof UploadedFile) {
                // Delete old file if exists
                if ($settings->$key) {
                    Storage::disk('public')->delete($settings->$key);
                }
                // Store new file
                $updateData[$key] = $data[$uf]->store('uploads/appearance', 'public');
            }
        }

        $settings->update($updateData);
    }

    /**
     * Reset appearance settings to factory defaults and delete old uploads.
     */
    public function resetToDefault(): void
    {
        $settings = AppearanceSetting::first();
        if ($settings) {
            $uploadFields = ['logo_path', 'logo_dark_path', 'favicon_path', 'login_background_path'];
            foreach ($uploadFields as $key) {
                if ($settings->$key) {
                    Storage::disk('public')->delete($settings->$key);
                }
            }
            $settings->delete();
        }

        // Re-initialize with defaults
        $this->getSettings();
    }
}
