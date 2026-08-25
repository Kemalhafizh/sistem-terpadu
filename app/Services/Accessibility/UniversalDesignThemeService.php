<?php

declare(strict_types=1);

namespace App\Services\Accessibility;

use App\Models\ThemePreference;
use App\Models\User;

class UniversalDesignThemeService
{
    /**
     * Get or set user theme and accessibility preferences
     */
    public function getPreferences(User $user): ThemePreference
    {
        return ThemePreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'color_mode' => 'system',
                'font_scaling' => '100%',
                'reduce_animations' => false,
                'dyslexic_font_enabled' => false,
                'primary_color_hex' => '#059669', // Emerald YBWSA
            ]
        );
    }

    /**
     * Update accessibility theme options
     */
    public function updatePreferences(User $user, array $data): ThemePreference
    {
        $pref = $this->getPreferences($user);
        $pref->update([
            'color_mode' => $data['color_mode'] ?? $pref->color_mode,
            'font_scaling' => $data['font_scaling'] ?? $pref->font_scaling,
            'reduce_animations' => $data['reduce_animations'] ?? $pref->reduce_animations,
            'dyslexic_font_enabled' => $data['dyslexic_font_enabled'] ?? $pref->dyslexic_font_enabled,
            'primary_color_hex' => $data['primary_color_hex'] ?? $pref->primary_color_hex,
        ]);

        return $pref;
    }
}
