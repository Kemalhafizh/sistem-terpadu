<?php

namespace App\Services;

use App\Models\DesignToken;
use App\Models\ThemePreset;
use App\Models\UserThemePreference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DesignSystemService
{
    /**
     * Get all master design tokens.
     */
    public function getDesignTokens(): array
    {
        return Cache::remember('design_tokens_all', 3600, function () {
            $tokens = DesignToken::all();
            if ($tokens->isEmpty()) {
                return $this->getDefaultTokens();
            }

            return $tokens->groupBy('category')->toArray();
        });
    }

    /**
     * Get default design tokens fallback.
     */
    public function getDefaultTokens(): array
    {
        return [
            'color' => [
                'primary' => ['light' => '#0D5C3A', 'dark' => '#1CB070'],
                'primary_container' => ['light' => '#EBFBF3', 'dark' => '#0A4D30'],
                'secondary' => ['light' => '#D4AF37', 'dark' => '#F7D97B'],
                'background' => ['light' => '#F8FAFC', 'dark' => '#080C14'],
                'surface' => ['light' => '#FFFFFF', 'dark' => '#0F172A'],
                'surface_variant' => ['light' => '#F1F5F9', 'dark' => '#1E293B'],
                'error' => ['light' => '#E11D48', 'dark' => '#F43F5E'],
            ],
            'spacing' => [
                'xs' => '4px',
                'sm' => '8px',
                'md' => '16px',
                'lg' => '24px',
                'xl' => '32px',
            ],
            'radius' => [
                'sm' => '8px',
                'md' => '16px',
                'lg' => '24px',
                'full' => '9999px',
            ],
            'typography' => [
                'font_family' => 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                'arabic_font_family' => '"Amiri", "Scheherazade New", serif',
            ],
        ];
    }

    /**
     * Get active school preset.
     */
    public function getActivePreset(): ThemePreset
    {
        $preset = ThemePreset::where('is_active', true)->orderByDesc('priority')->first();
        if (! $preset) {
            $preset = new ThemePreset([
                'name' => 'Sultan Agung Green (Default)',
                'slug' => 'default',
                'token_overrides' => [],
                'is_active' => true,
                'priority' => 0,
            ]);
        }

        return $preset;
    }

    /**
     * Resolve contextual preset based on time, active exam, or Ramadhan season.
     */
    public function getContextualPreset(?Carbon $now = null): string
    {
        $now = $now ?: Carbon::now('Asia/Jakarta');
        $hour = $now->hour;

        // 1. Check Exam Season (if exam active or UTS/UAS period)
        // Check if exams are currently active in DB
        $hasActiveExam = Schema::hasTable('exams')
            && DB::table('exams')
                ->where('is_active', true)
                ->whereDate('start_time', '<=', $now->toDateString())
                ->whereDate('end_time', '>=', $now->toDateString())
                ->exists();

        if ($hasActiveExam) {
            return 'exam_mode';
        }

        // 2. Check Night Study (18:00 - 05:00)
        if ($hour >= 18 || $hour < 5) {
            return 'night_study';
        }

        // 3. Check Ramadhan season (Placeholder check for Hijri month 9)
        if ($now->month == 3 || $now->month == 4) {
            // Seasonal spring / islamic calendar preset
            // return 'ramadhan_gold';
        }

        return 'default';
    }

    /**
     * Get user merged theme configuration.
     */
    public function getUserTheme(?int $userId = null): array
    {
        $contextualPreset = $this->getContextualPreset();
        $tokens = $this->getDesignTokens();

        if (! $userId) {
            return [
                'active_preset' => $contextualPreset,
                'is_dark' => $contextualPreset === 'night_study',
                'dynamic_color' => false,
                'auto_theme' => true,
                'tokens' => $tokens,
            ];
        }

        $pref = UserThemePreference::where('user_id', $userId)->first();
        if (! $pref || $pref->auto_theme) {
            return [
                'active_preset' => $contextualPreset,
                'is_dark' => $contextualPreset === 'night_study',
                'dynamic_color' => $pref ? $pref->dynamic_color : false,
                'auto_theme' => true,
                'tokens' => $tokens,
                'custom_overrides' => $pref ? $pref->custom_overrides : [],
            ];
        }

        return [
            'active_preset' => $pref->active_preset ?: $contextualPreset,
            'is_dark' => $pref->active_preset === 'night_study',
            'dynamic_color' => $pref->dynamic_color,
            'auto_theme' => false,
            'tokens' => $tokens,
            'custom_overrides' => $pref->custom_overrides ?: [],
        ];
    }

    /**
     * Update user theme preference.
     */
    public function updatePreferences(int $userId, array $data): UserThemePreference
    {
        return UserThemePreference::updateOrCreate(
            ['user_id' => $userId],
            [
                'active_preset' => $data['active_preset'] ?? null,
                'custom_overrides' => $data['custom_overrides'] ?? null,
                'auto_theme' => $data['auto_theme'] ?? true,
                'dynamic_color' => $data['dynamic_color'] ?? false,
            ]
        );
    }
}
