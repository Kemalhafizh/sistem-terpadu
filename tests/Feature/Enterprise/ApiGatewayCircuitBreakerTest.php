<?php

use App\Http\Middleware\RoleBasedRateLimiterMiddleware;
use App\Models\User;
use App\Services\CircuitBreakerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->cb = new CircuitBreakerService();
    $this->cb->resetCircuit('test_service');
});

test('circuit breaker service transitions from closed to open on 5 failures', function () {
    expect($this->cb->getServiceState('test_service'))->toBe(CircuitBreakerService::STATE_CLOSED);

    for ($i = 0; $i < 5; $i++) {
        $this->cb->recordFailure('test_service');
    }

    expect($this->cb->getServiceState('test_service'))->toBe(CircuitBreakerService::STATE_OPEN);
});

test('circuit breaker service executes fallback when open', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->cb->recordFailure('test_service');
    }

    $primaryCalled = false;
    $result = $this->cb->call(
        'test_service',
        function () use (&$primaryCalled) {
            $primaryCalled = true;
            return 'PRIMARY';
        },
        function ($reason) {
            return 'FALLBACK: ' . $reason;
        }
    );

    expect($primaryCalled)->toBeFalse();
    expect($result)->toContain('FALLBACK');
});

test('role based rate limiter middleware sets rate limit headers', function () {
    $middleware = new RoleBasedRateLimiterMiddleware();
    $request = Request::create('/api/v1/gateway/test', 'GET');

    $response = $middleware->handle($request, function () {
        return new Response('OK');
    });

    expect($response->headers->has('X-RateLimit-Limit'))->toBeTrue();
    expect($response->headers->has('X-RateLimit-Remaining'))->toBeTrue();
});

test('admin can view api gateway monitoring dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.observability.gateway.index'));
    $response->assertStatus(200);
    $response->assertSee('API Gateway');
});

test('admin can reset circuit and simulate failures via controller', function () {
    // Simulate failure
    $responseSimulate = $this->actingAs($this->admin)
        ->from(route('admin.observability.gateway.index'))
        ->post(route('admin.observability.gateway.simulate-failure'), [
            'service' => 'fonnte_whatsapp',
        ]);
    $responseSimulate->assertStatus(302);
    expect($this->cb->getServiceState('fonnte_whatsapp'))->toBe(CircuitBreakerService::STATE_OPEN);

    // Reset circuit
    $responseReset = $this->actingAs($this->admin)
        ->from(route('admin.observability.gateway.index'))
        ->post(route('admin.observability.gateway.reset'), [
            'service' => 'fonnte_whatsapp',
        ]);
    $responseReset->assertStatus(302);
    expect($this->cb->getServiceState('fonnte_whatsapp'))->toBe(CircuitBreakerService::STATE_CLOSED);
});
