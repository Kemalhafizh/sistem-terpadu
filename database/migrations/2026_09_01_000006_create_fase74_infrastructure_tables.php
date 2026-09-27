<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cloud_cluster_metrics')) {
            Schema::create('cloud_cluster_metrics', function (Blueprint $table) {
                $table->id();
                $table->string('node_name', 64);
                $table->string('cluster_zone', 64)->default('sulaone-cloud-dc1');
                $table->decimal('cpu_usage_percent', 5, 2);
                $table->decimal('memory_usage_percent', 5, 2);
                $table->integer('active_pod_replicas')->default(1);
                $table->integer('requests_per_second')->default(0);
                $table->decimal('p99_latency_ms', 6, 2)->default(15.0);
                $table->string('health_status', 32)->default('healthy'); // healthy, degraded, unhealthy
                $table->timestamps();

                $table->index(['cluster_zone', 'health_status']);
            });
        }

        if (! Schema::hasTable('edge_cache_nodes')) {
            Schema::create('edge_cache_nodes', function (Blueprint $table) {
                $table->id();
                $table->string('node_location', 128);
                $table->string('edge_ip', 45);
                $table->decimal('cache_hit_ratio', 5, 2)->default(95.0);
                $table->decimal('bandwidth_mbps', 8, 2)->default(100.0);
                $table->integer('cached_assets_count')->default(0);
                $table->boolean('hls_stream_active')->default(true);
                $table->string('status', 32)->default('online'); // online, standby, offline
                $table->timestamps();

                $table->index(['status', 'node_location']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('edge_cache_nodes');
        Schema::dropIfExists('cloud_cluster_metrics');
    }
};
