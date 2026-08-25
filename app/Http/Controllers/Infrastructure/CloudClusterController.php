<?php

declare(strict_types=1);

namespace App\Http\Controllers\Infrastructure;

use App\Http\Controllers\Controller;
use App\Services\Infrastructure\SulaoneCloudClusterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CloudClusterController extends Controller
{
    public function __construct(
        protected SulaoneCloudClusterService $clusterService
    ) {}

    public function getHealth(): JsonResponse
    {
        $health = $this->clusterService->getClusterHealth();

        return response()->json([
            'success' => true,
            'cluster' => $health,
        ], 200);
    }

    public function reportMetric(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'node_name' => 'required|string|max:64',
            'cpu_usage_percent' => 'required|numeric|between:0,100',
            'memory_usage_percent' => 'required|numeric|between:0,100',
            'requests_per_second' => 'required|integer|min:0',
            'p99_latency_ms' => 'nullable|numeric|min:0',
        ]);

        $metric = $this->clusterService->reportNodeMetric(
            $validated['node_name'],
            (float) $validated['cpu_usage_percent'],
            (float) $validated['memory_usage_percent'],
            (int) $validated['requests_per_second'],
            isset($validated['p99_latency_ms']) ? (float) $validated['p99_latency_ms'] : 15.0
        );

        return response()->json([
            'success' => true,
            'metric' => $metric,
        ], 200);
    }
}
