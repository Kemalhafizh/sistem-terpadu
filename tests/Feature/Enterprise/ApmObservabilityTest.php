<?php

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Models\User;
use App\Services\ApmTelemetryService;
use App\Services\Observability\RequestMetricsRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/*
 * The APM dashboard used to show fixed numbers (P50 35 / P90 65 / P99 98 ms,
 * uptime 99.98%), two invented slow queries, and a "trace" that returned the
 * same three spans and 47 ms for any id. Everything is now measured; these
 * tests record into a throwaway storage path.
 */
beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);

    $this->originalStoragePath = storage_path();
    $this->storageDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sista-apm-'.uniqid();
    File::ensureDirectoryExists($this->storageDir.DIRECTORY_SEPARATOR.'logs');
    app()->useStoragePath($this->storageDir);

    config(['apm.record_requests' => true]);
    $this->recorder = app(RequestMetricsRecorder::class);
});

afterEach(function () {
    app()->useStoragePath($this->originalStoragePath);
    File::deleteDirectory($this->storageDir);
});

function recordedLines(): array
{
    $path = app(RequestMetricsRecorder::class)->path();

    return is_file($path) ? array_values(array_filter(explode("\n", file_get_contents($path)))) : [];
}

// ── Correlation ID ───────────────────────────────────────────────────────

test('correlation id middleware injects X-Correlation-ID header into request and response', function () {
    $middleware = new CorrelationIdMiddleware;
    $request = Request::create('/test-tracing', 'GET');

    $response = $middleware->handle($request, function ($req) {
        expect($req->headers->has('X-Correlation-ID'))->toBeTrue();

        return new Response('OK');
    });

    expect($response->headers->has('X-Correlation-ID'))->toBeTrue();
});

test('correlation id dari klien dipertahankan jika valid dan diganti jika mengandung karakter berbahaya', function () {
    $middleware = new CorrelationIdMiddleware;

    $valid = Request::create('/x', 'GET', server: ['HTTP_X_CORRELATION_ID' => 'abc-123_DEF.4']);
    expect($middleware->handle($valid, fn () => new Response('OK'))->headers->get('X-Correlation-ID'))->toBe('abc-123_DEF.4');

    // A tab/newline would otherwise break the one-line-per-request log.
    $hostile = Request::create('/x', 'GET', server: ['HTTP_X_CORRELATION_ID' => "evil\tid\nforged"]);
    $replaced = $middleware->handle($hostile, fn () => new Response('OK'))->headers->get('X-Correlation-ID');
    expect($replaced)->not->toContain("\t")->not->toContain('evil')
        ->and(Str::isUuid($replaced))->toBeTrue();
});

// ── Recording real requests ───────────────────────────────────────────────

test('setiap request API dan web tercatat tanpa query string', function () {
    $api = $this->getJson('/api/v1/spmb/waves?token=RAHASIA123');
    $api->assertOk();
    $this->actingAs($this->admin)->get(route('admin.observability.apm.index'))->assertOk();

    $lines = recordedLines();
    expect($lines)->toHaveCount(2);

    [$at, $duration, $status, $method, $path, $correlationId] = explode("\t", $lines[0]);
    expect((int) $status)->toBe(200)
        ->and($method)->toBe('GET')
        ->and($path)->toBe('/api/v1/spmb/waves')
        ->and((float) $duration)->toBeGreaterThan(0)
        // The same id the client received, so it can be looked up later.
        ->and($correlationId)->toBe($api->headers->get('X-Correlation-ID'));

    expect(implode("\n", $lines))->not->toContain('RAHASIA123');
    expect(explode("\t", $lines[1])[4])->toBe('/admin/observability/apm');
});

test('pencatatan mati secara default di test suite', function () {
    config(['apm.record_requests' => false]);
    $this->getJson('/api/v1/spmb/waves')->assertOk();

    expect(recordedLines())->toBe([]);
});

test('file catatan dipangkas ke baris terbaru saat melebihi batas', function () {
    config(['apm.max_bytes' => 200, 'apm.keep_lines' => 5]);
    for ($i = 1; $i <= 20; $i++) {
        $this->recorder->record('GET', "/r/{$i}", 200, $i, "id-{$i}");
    }

    $paths = array_map(fn ($l) => explode("\t", $l)[4], recordedLines());
    expect(count($paths))->toBeLessThanOrEqual(5)
        ->and(end($paths))->toBe('/r/20');
});

// ── Summary figures ─────────────────────────────────────────────────────

test('persentil dihitung dengan metode nearest-rank', function () {
    $values = range(1, 100); // 1..100 ms
    expect(RequestMetricsRecorder::percentile($values, 50))->toBe(50.0)
        ->and(RequestMetricsRecorder::percentile($values, 90))->toBe(90.0)
        ->and(RequestMetricsRecorder::percentile($values, 99))->toBe(99.0)
        ->and(RequestMetricsRecorder::percentile([42], 99))->toBe(42.0)
        ->and(RequestMetricsRecorder::percentile([], 50))->toBeNull();
});

test('ringkasan memakai request nyata dalam jendela waktu dan menghitung ketersediaan', function () {
    $now = microtime(true);
    // 99 fast OK requests + 1 slow 5xx within the window…
    for ($i = 1; $i <= 99; $i++) {
        $this->recorder->record('GET', '/ok', 200, $i, "ok-{$i}", $now - 60);
    }
    $this->recorder->record('POST', '/boom', 500, 900, 'boom', $now - 30);
    // …and one from two days ago that must be ignored.
    $this->recorder->record('GET', '/old', 500, 5000, 'old', $now - 2 * 86400);

    $summary = $this->recorder->summary($now);

    expect($summary['sample_count'])->toBe(100)
        ->and($summary['p50'])->toBe(50.0)
        ->and($summary['p90'])->toBe(90.0)
        ->and($summary['p99'])->toBe(99.0)
        ->and($summary['error_count'])->toBe(1)
        ->and($summary['availability'])->toBe(99.0);

    $sla = app(ApmTelemetryService::class)->getTelemetryMetrics()['sla'];
    // 1% of requests failed against a 0.1% budget → budget exhausted.
    expect($sla['availability'])->toBe(99.0)
        ->and($sla['status'])->toBe('BELOW TARGET')
        ->and($sla['error_budget_remaining'])->toBe(0.0)
        ->and($sla['uptime'])->toBeNull();
});

test('error budget utuh saat tidak ada respons 5xx', function () {
    foreach ([12, 20, 35] as $ms) {
        $this->recorder->record('GET', '/ok', 200, $ms, null);
    }
    $metrics = app(ApmTelemetryService::class)->getTelemetryMetrics();

    expect($metrics['sla']['availability'])->toBe(100.0)
        ->and($metrics['sla']['status'])->toBe('MEETS TARGET')
        ->and($metrics['sla']['error_budget_remaining'])->toBe(100.0)
        ->and($metrics['latency']['p99'])->toBe(35.0)
        ->and($metrics['latency']['is_p99_healthy'])->toBeTrue();
});

test('tanpa data tidak ada angka karangan', function () {
    $metrics = app(ApmTelemetryService::class)->getTelemetryMetrics();

    expect($metrics['latency']['p50'])->toBeNull()
        ->and($metrics['latency']['p99'])->toBeNull()
        ->and($metrics['latency']['is_p99_healthy'])->toBeNull()
        ->and($metrics['sla']['availability'])->toBeNull()
        ->and($metrics['sla']['uptime'])->toBeNull()
        ->and($metrics['sla']['status'])->toBe('NO DATA')
        ->and($metrics['memory']['used_mb'])->toBeGreaterThan(0)
        ->and(app(ApmTelemetryService::class)->getSlowQueries())->toBe([]);
});

// ── Slow queries & lookup ─────────────────────────────────────────────────

test('query lambat berasal dari log PerformanceLogger yang nyata', function () {
    file_put_contents(storage_path('logs/observability.json'), json_encode([
        'timestamp' => '2026-09-26T10:00:00+07:00',
        'type' => 'performance',
        'url' => '/api/v1/student/grades',
        'method' => 'GET',
        'correlation_id' => 'lambat-1',
        'duration_ms' => 1500,
        'slow_queries' => [['sql' => 'select * from grades where student_id = ?', 'time_ms' => 812.4]],
    ])."\n");

    $slow = app(ApmTelemetryService::class)->getSlowQueries();

    expect($slow)->toHaveCount(1)
        ->and($slow[0]['query'])->toBe('select * from grades where student_id = ?')
        ->and($slow[0]['duration_ms'])->toBe(812.4)
        ->and($slow[0]['caller'])->toBe('/api/v1/student/grades');
});

test('admin can view enterprise apm dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.observability.apm.index'));
    $response->assertStatus(200);
    $response->assertSee('Enterprise APM');
    // Nothing recorded yet (this request is logged only after it finishes).
    $response->assertSee('Belum ada request tercatat');
    $response->assertSee('Belum ada query lambat yang tercatat.');
    $response->assertDontSee('99.98');
    $response->assertDontSee('SQ-002');
});

test('pencarian correlation id mengembalikan request yang benar-benar tercatat', function () {
    $api = $this->getJson('/api/v1/spmb/waves')->assertOk();
    $id = $api->headers->get('X-Correlation-ID');
    file_put_contents(storage_path('logs/observability.json'), json_encode([
        'timestamp' => now()->toIso8601String(),
        'type' => 'performance',
        'url' => '/api/v1/spmb/waves',
        'correlation_id' => $id,
        'slow_queries' => [['sql' => 'select * from spmb_waves', 'time_ms' => 640]],
    ])."\n");

    $response = $this->actingAs($this->admin)->getJson(route('admin.observability.apm.trace', ['correlation_id' => $id]));

    $response->assertOk()
        ->assertJsonPath('found', true)
        ->assertJsonPath('correlation_id', $id)
        ->assertJsonPath('requests.0.path', '/api/v1/spmb/waves')
        ->assertJsonPath('requests.0.status', 200)
        ->assertJsonPath('slow_queries.0.sql', 'select * from spmb_waves');
    expect($response->json('total_latency_ms'))->toBeGreaterThan(0)
        ->and($response->json('spans'))->toBeNull();
});

test('correlation id yang tidak pernah ada tidak lagi menghasilkan trace karangan', function () {
    $this->actingAs($this->admin)
        ->getJson(route('admin.observability.apm.trace', ['correlation_id' => 'TRACE-TEST-UUID-1234']))
        ->assertStatus(404)
        ->assertJsonPath('found', false);

    $this->actingAs($this->admin)
        ->getJson(route('admin.observability.apm.trace', ['correlation_id' => 'ada spasi']))
        ->assertStatus(422);
});
