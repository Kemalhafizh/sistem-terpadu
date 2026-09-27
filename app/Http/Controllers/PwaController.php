<?php

namespace App\Http\Controllers;

use App\Services\PwaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PwaController extends Controller
{
    protected PwaService $pwaService;

    public function __construct(PwaService $pwaService)
    {
        $this->pwaService = $pwaService;
    }

    /**
     * GET /manifest.json
     */
    public function manifest(): JsonResponse
    {
        return response()->json(
            $this->pwaService->getManifest(),
            200,
            ['Content-Type' => 'application/manifest+json; charset=utf-8']
        );
    }

    /**
     * GET /sw.js
     */
    public function serviceWorker()
    {
        $path = public_path('sw.js');
        if (file_exists($path)) {
            return response(file_get_contents($path), 200, [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Service-Worker-Allowed' => '/',
            ]);
        }

        $defaultSw = <<<'JS'
const CACHE_NAME = 'sista-v2.4.0';
self.addEventListener('install', (event) => {
    self.skipWaiting();
});
self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});
self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
JS;

        return response($defaultSw, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    /**
     * POST /pwa/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Autentikasi diperlukan untuk mendaftarkan web push notification.');
        }

        $result = $this->pwaService->registerWebPush($user->id, $request->all());

        return response()->json($result);
    }
}
