<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\OfflineSyncService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    use ApiResponseTrait;

    protected OfflineSyncService $syncService;

    public function __construct(OfflineSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * GET /api/v1/sync/delta?since={timestamp}
     */
    public function getDelta(Request $request)
    {
        $since = (int) $request->query('since', 0);
        $delta = $this->syncService->getDeltaChanges($since);
        return $this->successResponse($delta, 'Delta changes retrieved successfully');
    }

    /**
     * POST /api/v1/sync/batch
     */
    public function processBatch(Request $request)
    {
        $validated = $request->validate([
            'actions' => 'required|array|max:100',
            'actions.*.id' => 'required|string|max:100',
            'actions.*.type' => 'required|string|max:50',
            'actions.*.data' => 'nullable|array',
        ]);

        $user = $request->user();
        $result = $this->syncService->processBatchUpload($validated, $user);
        return $this->successResponse($result, 'Batch offline actions processed');
    }

    /**
     * GET /api/v1/sync/check-version
     */
    public function checkVersion()
    {
        $version = $this->syncService->checkVersion();
        return $this->successResponse($version, 'Sync version verified');
    }

    /**
     * GET /api/v1/sync/catch-up?last_event_id=...
     */
    public function catchUp(Request $request)
    {
        $lastEventId = $request->query('last_event_id', '0');
        $events = $this->syncService->getCatchUpEvents($lastEventId);
        return $this->successResponse($events, 'Catch-up events retrieved successfully');
    }
}
