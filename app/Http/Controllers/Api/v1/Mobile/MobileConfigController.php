<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobileAppConfig;
use App\Support\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MobileConfigController
 *
 * Provides dynamic runtime configuration, minimum build version gates,
 * maintenance mode states, and feature flags for Android and iOS clients.
 */
class MobileConfigController extends Controller
{
    /**
     * Retrieve remote app configuration and calculate version enforcement.
     */
    public function getConfig(Request $request): JsonResponse
    {
        $config = MobileAppConfig::first();

        if (! $config) {
            $config = MobileAppConfig::create([
                'app_name' => 'SISTA '.school_short_name(),
                'min_android_build' => 100,
                'latest_android_build' => 105,
                'min_ios_build' => 100,
                'latest_ios_build' => 105,
                'force_update' => false,
                'maintenance_mode' => false,
                'maintenance_message' => 'Sistem sedang dalam peningkatan performa rutin.',
                'play_store_url' => 'https://play.google.com/store/apps/details?id=sch.id.sultanagung1.sista',
                'app_store_url' => 'https://apps.apple.com/app/sista-sultan-agung-1/id6400000000',
                'active_features' => [
                    'academic_grades' => true,
                    'spp_payment_gateway' => true,
                    'tahfidz_quran' => true,
                    'cbt_exam_mode' => true,
                    'biometric_login' => true,
                    'offline_sync' => true,
                    'dark_mode' => true,
                ],
            ]);
        }

        $platform = strtolower((string) $request->query('platform', 'android'));
        $currentBuild = (int) $request->query('build', 105);

        $minBuild = $platform === 'ios' ? $config->min_ios_build : $config->min_android_build;
        $latestBuild = $platform === 'ios' ? $config->latest_ios_build : $config->latest_android_build;

        $updateRequired = $currentBuild < $minBuild;
        $updateAvailable = $currentBuild < $latestBuild;

        return response()->json([
            'success' => true,
            'app_name' => $config->app_name,
            'server_time' => now()->toIso8601String(),
            'maintenance' => [
                'is_maintenance' => (bool) $config->maintenance_mode,
                'message' => $config->maintenance_message,
            ],
            'version_check' => [
                'current_client_build' => $currentBuild,
                'minimum_required_build' => $minBuild,
                'latest_available_build' => $latestBuild,
                'update_required' => $updateRequired,
                'update_available' => $updateAvailable,
                'store_url' => $platform === 'ios' ? $config->app_store_url : $config->play_store_url,
            ],
            'features' => $config->active_features,
            'contact_support' => [
                // Was a placeholder number and an address the school doesn't use.
                'whatsapp' => School::whatsapp(),
                'email' => School::email(),
            ],
            // The school's location (map pin). removed GPS check-in, so
            // nothing enforces the radius any more.
            'campus' => School::campus(),
            // The school's identity for the app's "Tentang" page, from the same
            // settings the letters and the website use. Empty fields stay null.
            'school' => [
                'name' => School::name(),
                'short_name' => School::shortName(),
                'foundation' => School::foundationName(),
                'npsn' => School::npsn(),
                'accreditation' => School::accreditation(),
                'address' => School::address(),
                'phone' => School::phone(),
                'email' => School::email(),
                'website' => School::website(),
            ],
        ], 200);
    }
}
