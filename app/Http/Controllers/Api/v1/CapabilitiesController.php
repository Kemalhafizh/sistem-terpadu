<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Support\Access\MobileCapabilities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * What the signed-in user may use in the app. The app builds its menus from
 * this list (never from a role it guesses locally); the server still judges
 * every request on its own, so hiding a feature is not the only guard.
 */
class CapabilitiesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $features = MobileCapabilities::for($user);
        $keys = array_column($features, 'key');

        return response()->json([
            'success' => true,
            'message' => 'Daftar fitur untuk akun ini.',
            'data' => [
                'role' => $user->role,
                'features' => $features,
                'groups' => collect(MobileCapabilities::groups())
                    ->map(fn (string $title, string $key) => ['key' => $key, 'title' => $title])
                    ->values(),
                // Changes whenever the set of features changes, so the app can
                // cache the list and notice when it must rebuild its menus.
                'version' => substr(sha1(implode(',', $keys)), 0, 12),
            ],
        ]);
    }
}
