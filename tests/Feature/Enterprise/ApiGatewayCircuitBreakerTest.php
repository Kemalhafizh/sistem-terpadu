<?php

use App\Http\Middleware\RoleBasedRateLimiterMiddleware;
use App\Models\User;
use App\Services\CircuitBreakerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->cb = new CircuitBreakerService;
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
            return 'FALLBACK: '.$reason;
        }
    );

    expect($primaryCalled)->toBeFalse();
    expect($result)->toContain('FALLBACK');
});

test('role based rate limiter middleware sets rate limit headers', function () {
    $middleware = new RoleBasedRateLimiterMiddleware;
    $request = Request::create('/api/v1/gateway/test', 'GET');

    $response = $middleware->handle($request, function () {
        return new Response('OK');
    });

    expect($response->headers->has('X-RateLimit-Limit'))->toBeTrue();
    expect($response->headers->has('X-RateLimit-Remaining'))->toBeTrue();
});
