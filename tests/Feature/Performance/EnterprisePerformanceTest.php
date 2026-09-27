<?php

use App\Models\User;
use App\Services\Performance\AssetOptimizationService;
use App\Services\Performance\LoadTestService;
use App\Services\Performance\QueryOptimizerService;
use App\Services\Performance\ScalingConfigService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Performance Engineer']);
    $this->queryService = app(QueryOptimizerService::class);
    $this->assetService = app(AssetOptimizationService::class);
    $this->loadService = app(LoadTestService::class);
    $this->scalingService = app(ScalingConfigService::class);
});

// 32.1 Database Query Optimizer & N+1 Sentinel
test('query optimizer service logs slow query and detects n plus one patterns', function () {
    $log = $this->queryService->logSlowQuery('test.route', 'select * from users where id = 1', 250.0);
    expect($log->is_slow)->toBeTrue();

    $queries = [
        ['sql' => 'select * from students where id = 1'],
        ['sql' => 'select * from students where id = 1'],
        ['sql' => 'select * from students where id = 1'],
        ['sql' => 'select * from students where id = 1'],
    ];
    $nPlusOne = $this->queryService->detectNPlusOne($queries);
    expect($nPlusOne)->toHaveCount(1);

    $report = $this->queryService->generateOptimizationReport();
    expect($report['total_queries_analyzed'])->toBeGreaterThan(0);

    $dashResponse = $this->actingAs($this->user)->get(route('query_performance.dashboard'));
    $dashResponse->assertStatus(200);

    $slowResponse = $this->actingAs($this->user)->get(route('query_performance.slow'));
    $slowResponse->assertStatus(200);
});

// 32.2 Lazy Loading, Code Splitting & Asset Optimization
test('asset optimization service analyzes bundle and generates web vitals report', function () {
    $report = $this->assetService->generateReport();
    expect($report['performance_grade'])->toContain('Enterprise');
    expect($report['core_web_vitals']['lcp_seconds'])->toBeGreaterThan(0);

    $assetResponse = $this->actingAs($this->user)->get(route('asset_performance.report'));
    $assetResponse->assertStatus(200);
});

// 32.3 Automated Stress Test & Load Testing Suite
test('load test service executes scenario and compares baseline', function () {
    $result = $this->loadService->runScenario('Ujian CBT Massal', 100, 10);
    expect($result->id)->not()->toBeNull();
    expect($result->status)->toBe('completed');

    $comparison = $this->loadService->compareResults($result->id);
    expect($comparison['current'])->not()->toBeNull();

    $dashResponse = $this->actingAs($this->user)->get(route('load_test.dashboard'));
    $dashResponse->assertStatus(200);

    $runResponse = $this->actingAs($this->user)->post(route('load_test.run'), [
        'scenario_name' => 'Stress Test Controller',
        'concurrent_users' => 50,
        'duration_seconds' => 10,
    ]);
    $runResponse->assertRedirect();
});

// 32.4 Horizontal Scaling Architecture Blueprint & Read Replicas
test('scaling config service exposes topology and cluster health status', function () {
    $replicaConfig = $this->scalingService->getReadReplicaConfig();
    expect($replicaConfig['read_replica_enabled'])->toBeTrue();

    $health = $this->scalingService->healthCheck();
    expect($health['database_cluster'])->toBe('healthy');

    $scalingDashResponse = $this->actingAs($this->user)->get(route('scaling.dashboard'));
    $scalingDashResponse->assertStatus(200);

    $configResponse = $this->actingAs($this->user)->get(route('scaling.configuration'));
    $configResponse->assertStatus(200);
});
