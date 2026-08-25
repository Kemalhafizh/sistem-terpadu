<?php

declare(strict_types=1);

namespace App\Http\Controllers\Infrastructure;

use App\Http\Controllers\Controller;
use App\Models\EdgeCacheNode;
use App\Services\Infrastructure\DedicatedEdgeCdnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EdgeCdnController extends Controller
{
    public function __construct(
        protected DedicatedEdgeCdnService $cdnService
    ) {}

    public function resolveAsset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_path' => 'required|string',
        ]);

        $resolved = $this->cdnService->resolveOptimalAssetUrl($validated['asset_path'], $request->ip());

        return response()->json([
            'success' => true,
            'data' => $resolved,
        ], 200);
    }

    public function getNodes(): JsonResponse
    {
        $nodes = EdgeCacheNode::all();

        return response()->json([
            'success' => true,
            'data' => $nodes,
        ], 200);
    }
}
