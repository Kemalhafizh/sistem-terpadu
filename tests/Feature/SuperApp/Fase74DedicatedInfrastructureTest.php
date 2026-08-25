<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase74DedicatedInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_sulaone_private_cloud_cluster_health_and_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Report Metric
        $metricRes = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/infrastructure/cluster/metrics', [
            'node_name' => 'worker-node-01',
            'cpu_usage_percent' => 65.5,
            'memory_usage_percent' => 58.2,
            'requests_per_second' => 320,
            'p99_latency_ms' => 12.4,
        ]);
        $metricRes->assertStatus(200);
        $this->assertEquals(2, $metricRes->json('metric.active_pod_replicas')); // Autoscaled to 2 replicas

        // Get Cluster Health
        $healthRes = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/infrastructure/cluster/health');
        $healthRes->assertStatus(200);
        $healthRes->assertJsonPath('cluster.cluster_status', 'ONLINE');
        $this->assertEquals(99.99, $healthRes->json('cluster.uptime_sla_percent'));
    }

    public function test_dedicated_edge_cdn_asset_resolution(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $res = $this->actingAs($user, 'sanctum')->postJson('/api/v1/infrastructure/cdn/resolve', [
            'asset_path' => '/media/tahsin/juz30/surah_an_naba_master.m3u8',
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('data.is_hls_stream', true);
        $this->assertStringContainsString('m3u8', $res->json('data.cdn_edge_url'));
    }
}
