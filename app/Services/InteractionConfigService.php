<?php

namespace App\Services;

use App\Models\UserAnimationPreference;
use Illuminate\Support\Facades\Cache;

class InteractionConfigService
{
    /**
     * Get animation and micro-interaction configuration for user.
     */
    public function getAnimationConfig(int $userId): array
    {
        return Cache::remember("user_{$userId}_animation_config", 3600, function () use ($userId) {
            $pref = UserAnimationPreference::where('user_id', $userId)->first();
            if ($pref) {
                return [
                    'reduce_motion' => $pref->reduce_motion,
                    'haptic_level' => $pref->haptic_level,
                    'skeleton_loading' => $pref->skeleton_loading,
                    'spring_animations' => $pref->spring_animations,
                    'parallax_effects' => $pref->parallax_effects,
                    'page_transitions' => $pref->page_transitions,
                    'pull_refresh_style' => $pref->pull_refresh_style,
                ];
            }

            return $this->getDefaultConfig();
        });
    }

    /**
     * Return WCAG / Material 3 compliant defaults.
     */
    public function getDefaultConfig(): array
    {
        return [
            'reduce_motion' => false,
            'haptic_level' => 'medium',
            'skeleton_loading' => true,
            'spring_animations' => true,
            'parallax_effects' => true,
            'page_transitions' => 'slide',
            'pull_refresh_style' => 'physics',
        ];
    }

    /**
     * Update user animation preferences.
     */
    public function updatePreferences(int $userId, array $data): UserAnimationPreference
    {
        $pref = UserAnimationPreference::updateOrCreate(
            ['user_id' => $userId],
            [
                'reduce_motion' => $data['reduce_motion'] ?? false,
                'haptic_level' => $data['haptic_level'] ?? 'medium',
                'skeleton_loading' => $data['skeleton_loading'] ?? true,
                'spring_animations' => $data['spring_animations'] ?? true,
                'parallax_effects' => $data['parallax_effects'] ?? true,
                'page_transitions' => $data['page_transitions'] ?? 'slide',
                'pull_refresh_style' => $data['pull_refresh_style'] ?? 'physics',
            ]
        );

        Cache::forget("user_{$userId}_animation_config");

        return $pref;
    }
}
