<?php

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Models\User;
use App\Services\ApmTelemetryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->telemetryService = new ApmTelemetryService();
});

test('correlation id middleware injects X-Correlation-ID header into request and response', function () {
    $middleware = new CorrelationIdMiddleware();
    $request = Request::create('/test-tracing', 'GET');

    $response = $middleware->handle($request, function ($req) {
        expect($req->headers->has('X-Correlation-ID'))->toBeTrue();
        return new Response('OK');
    });

    expect($response->headers->has('X-Correlation-ID'))->toBeTrue();
});

test('apm telemetry service calculates latency percentiles and memory usage', function () {
    $metrics = $this->telemetryService->getTelemetryMetrics();

    expect($metrics['latency']['p99'])->toBeLessThanOrEqual(100.0);
    expect($metrics['memory']['used_mb'])->toBeGreaterThan(0);
    expect($metrics['sla']['target'])->toBe(99.90);

    $slowQueries = $this->telemetryService->getSlowQueries();
    expect(count($slowQueries))->toBeGreaterThan(0);
});

test('admin can view enterprise apm dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.observability.apm.index'));
    $response->assertStatus(200);
    $response->assertSee('Enterprise APM');
    $response->assertSee('P99 TARGET');
});

test('admin can simulate distributed trace lookup by correlation id', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.observability.apm.trace', [
        'correlation_id' => 'TRACE-TEST-UUID-1234',
    ]));

    $response->assertStatus(200);
    $response->assertJson([
        'correlation_id' => 'TRACE-TEST-UUID-1234',
        'total_latency_ms' => 47.0,
    ]);
});
