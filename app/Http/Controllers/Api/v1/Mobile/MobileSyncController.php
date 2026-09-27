<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MobileSyncController
 * 
 * Provides delta-syncing endpoints for mobile applications, allowing client-side
 * SQLite / Room / CoreData caches to pull only updated and new records.
 * 
 * @package App\Http\Controllers\Api\v1\Mobile
 */
class MobileSyncController extends Controller
{
    /**
     * @param MobileSyncService $syncService
     */
    public function __construct(
        protected MobileSyncService $syncService
    ) {}

    /**
     * Pull delta changes since a specific timestamp.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pullDelta(Request $request): JsonResponse
    {
        $since = $request->query('since');
        $deviceId = $request->header('X-Device-ID', 'default-device');

        $result = $this->syncService->pullDelta($request->user(), $since ? (string) $since : null, (string) $deviceId);

        return response()->json($result, 200);
    }
}
