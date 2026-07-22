<?php

use App\Http\Middleware\HttpCacheHeadersMiddleware;
use App\Models\User;
use App\Services\EnterpriseCacheService;
use App\Services\RequestLifetimeCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->cacheService = new EnterpriseCacheService();
    $this->requestCache = new RequestLifetimeCacheService();
});

test('request lifetime cache singleton stores items per request', function () {
    $counter = 0;
    $callback = function () use (&$counter) {
        $counter++;
        return 'test_value';
    };

    $val1 = $this->requestCache->remember('key_1', $callback);
    $val2 = $this->requestCache->remember('key_1', $callback);

    expect($val1)->toBe('test_value');
    expect($val2)->toBe('test_value');
    expect($counter)->toBe(1); // Callback only executed once

    $this->requestCache->flush();
    expect($this->requestCache->count())->toBe(0);
});

test('enterprise cache service remembers and flushes tag groups', function () {
    $val = $this->cacheService->remember('test_tag_key', 3600, fn() => 'tag_data', ['academic']);
    expect($val)->toBe('tag_data');

    $flushed = $this->cacheService->flushTag('academic');
    expect($flushed)->toBeTrue();
});

test('http cache headers middleware sets etag and returns 304 on match', function () {
    $middleware = new HttpCacheHeadersMiddleware();
    $request = Request::create('/test-page', 'GET');

    $response = $middleware->handle($request, function ($req) {
        return new Response('<html><body>Test Page Content</body></html>');
    });

    expect($response->headers->has('ETag'))->toBeTrue();
    $eTag = $response->headers->get('ETag');

    // Request with matching If-None-Match
    $request304 = Request::create('/test-page', 'GET');
    $request304->headers->set('If-None-Match', $eTag);

    $response304 = $middleware->handle($request304, function ($req) {
        return new Response('<html><body>Test Page Content</body></html>');
    });

    expect($response304->getStatusCode())->toBe(304);
});

test('admin can view cache monitoring dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.observability.cache.index'));
    $response->assertStatus(200);
    $response->assertSee('Multi-Layer Distributed Caching');
});

test('admin can flush tag group via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.observability.cache.index'))
        ->post(route('admin.observability.cache.flush-tag'), [
            'tag' => 'finance',
        ]);
    $response->assertStatus(302);
    $response->assertRedirect(route('admin.observability.cache.index'));
});

test('admin can warmup enterprise cache via controller', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.observability.cache.warmup'));
    $response->assertStatus(302);
});
