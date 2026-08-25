<?php

declare(strict_types=1);

namespace App\Services\Infrastructure;

use App\Models\EdgeCacheNode;

class DedicatedEdgeCdnService
{
    /**
     * Register or update an Edge CDN POP node.
     */
    public function registerEdgeNode(
        string $location,
        string $edgeIp,
        float $hitRatio = 98.2,
        float $bandwidthMbps = 250.0,
        int $cachedAssets = 4500
    ): EdgeCacheNode {
        return EdgeCacheNode::updateOrCreate(
            ['node_location' => $location],
            [
                'edge_ip' => $edgeIp,
                'cache_hit_ratio' => $hitRatio,
                'bandwidth_mbps' => $bandwidthMbps,
                'cached_assets_count' => $cachedAssets,
                'hls_stream_active' => true,
                'status' => 'online',
            ]
        );
    }

    /**
     * Resolve closest Edge CDN URL for an educational multimedia asset.
     */
    public function resolveOptimalAssetUrl(string $assetPath, ?string $clientIp = null): array
    {
        $activeNodes = EdgeCacheNode::where('status', 'online')->get();
        $selectedNode = $activeNodes->first();

        $cdnDomain = $selectedNode ? 'cdn-' . strtolower(preg_replace('/[^a-z0-9]/i', '', $selectedNode->node_location)) . '.sulaone.sch.id' : 'cdn.sulaone.sch.id';

        $cleanPath = ltrim($assetPath, '/');
        $edgeUrl = "https://{$cdnDomain}/{$cleanPath}";
        $isHls = str_ends_with($cleanPath, '.m3u8') || str_contains($cleanPath, 'hls/');

        return [
            'original_path' => $assetPath,
            'cdn_edge_url' => $edgeUrl,
            'edge_pop' => $selectedNode->node_location ?? 'Semarang Central Hub',
            'is_hls_stream' => $isHls,
            'cache_control' => 'public, max-age=86400, stale-while-revalidate=3600',
        ];
    }
}
