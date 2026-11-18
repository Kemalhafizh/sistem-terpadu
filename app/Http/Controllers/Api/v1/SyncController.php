<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\OfflineSyncService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        // Last-Modified reflects this delta's own real
        // "as of" instant (sync_timestamp is Carbon::now() at compute
        // time — see OfflineSyncService::getDeltaChanges()), not a
        // fabricated value.
        $lastModified = Carbon::createFromTimestampMs($delta['sync_timestamp']);

        return $this->successResponseWithValidators($delta, $lastModified, 'Delta changes retrieved successfully');
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
        // NOTE (found while wiring FASE 74.2 network validation): the
        // underlying stream here is still a hardcoded placeholder — see
        // OfflineSyncService::getCatchUpEvents() — so in practice this
        // event id is always distinct only for that one hardcoded test
        // id. The ETag below is still real (hashed off whatever this
        // call actually returns), it just won't vary much until the
        // Redis Streams backing store is implemented.
        $events = $this->syncService->getCatchUpEvents($lastEventId);

        return $this->successResponseWithValidators($events, null, 'Catch-up events retrieved successfully');
    }
}
