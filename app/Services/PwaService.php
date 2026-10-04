<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PwaService
{
    /**
     * Get PWA Web App Manifest JSON configuration.
     */
    public function getManifest(): array
    {
        return [
            'name' => 'SISTA — '.school_name(),
            'short_name' => 'Sulaone',
            'description' => 'SuperApp Ekosistem Sekolah Terpadu '.school_name(),
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#080C14',
            'theme_color' => '#0D5C3A',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => '/assets/icons/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => '/assets/icons/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
            'categories' => ['education', 'productivity'],
            'lang' => 'id',
        ];
    }

    /**
     * Get Service Worker caching configuration.
     */
    public function getServiceWorkerConfig(): array
    {
        return [
            'version' => 'v2.4.0',
            'cache_name' => 'sista-cache-v2',
            'cache_first_routes' => [
                '/assets/css/',
                '/assets/js/',
                '/assets/fonts/',
                '/assets/images/',
            ],
            'network_first_routes' => [
                '/api/v1/home/contextual',
                '/api/v1/gamification/profile',
                '/api/v1/notifications',
            ],
            'stale_while_revalidate_routes' => [
                '/dashboard',
                '/announcements',
                '/calendar',
            ],
        ];
    }

    /**
     * Register browser Web Push subscription.
     */
    public function registerWebPush(int $userId, array $subscription): array
    {
        $subscriptions = Cache::get("user_{$userId}_web_push", []);
        $subscriptions[] = [
            'endpoint' => $subscription['endpoint'] ?? '',
            'keys' => $subscription['keys'] ?? [],
            'created_at' => now()->toIso8601String(),
        ];
        Cache::put("user_{$userId}_web_push", $subscriptions, 86400 * 30);

        return [
            'success' => true,
            'message' => 'Web push subscription registered successfully.',
        ];
    }
}
