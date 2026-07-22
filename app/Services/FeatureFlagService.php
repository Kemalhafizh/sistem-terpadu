<?php

namespace App\Services;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class FeatureFlagService
{
    /**
     * Check if a feature flag is enabled for optional user context.
     */
    public function isEnabled(string $flagKey, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        $flag = Cache::remember("feature_flag:{$flagKey}", 300, function () use ($flagKey) {
            return FeatureFlag::where('key', $flagKey)->first();
        });

        if (! $flag || ! $flag->is_active) {
            return false;
        }

        // Target Roles evaluation
        if (! empty($flag->target_roles) && is_array($flag->target_roles)) {
            $userRole = $user->role ?? 'guest';
            if (! in_array($userRole, $flag->target_roles)) {
                return false;
            }
        }

        // Percentage Rollout evaluation
        if ($flag->rollout_percentage < 100 && $user) {
            $hash = abs(crc32("{$flagKey}:{$user->id}")) % 100;
            if ($hash >= $flag->rollout_percentage) {
                return false;
            }
        }

        return true;
    }

    /**
     * Toggle active status of a feature flag.
     */
    public function toggle(string $flagKey, ?bool $isActive = null): bool
    {
        $flag = FeatureFlag::where('key', $flagKey)->firstOrFail();
        $flag->is_active = $isActive ?? ! $flag->is_active;
        $flag->save();

        Cache::forget("feature_flag:{$flagKey}");

        return $flag->is_active;
    }

    /**
     * Update rollout percentage and target roles of a feature flag.
     */
    public function updateFlag(string $flagKey, int $rolloutPercentage, ?array $targetRoles = null): FeatureFlag
    {
        $flag = FeatureFlag::where('key', $flagKey)->firstOrFail();
        $flag->rollout_percentage = max(0, min(100, $rolloutPercentage));
        $flag->target_roles = $targetRoles;
        $flag->save();

        Cache::forget("feature_flag:{$flagKey}");

        return $flag;
    }
}
