<?php

use App\Models\User;
use App\Services\SecurityAuditService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

test('guest is redirected to login when accessing security audit page', function () {
    $response = $this->get(route('admin.security.audit'));
    $response->assertRedirect(route('login'));
});

test('non-admin user is forbidden from accessing security audit page', function () {
    $user = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $response = $this->actingAs($user)->get(route('admin.security.audit'));
    $response->assertStatus(403);
});

test('admin can access security audit page and see compliance gauge', function () {
    $user = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $response = $this->actingAs($user)->get(route('admin.security.audit'));
    $response->assertOk();
    $response->assertSee('Skor Kepatuhan Keamanan');
    $response->assertSee('Environment & Konfigurasi', false);
    $response->assertSee('Keamanan Sesi Cookie');
    $response->assertSee('Aturan Sandi (Password Policy)');
});

test('response contains required security headers', function () {
    $user = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $response = $this->actingAs($user)->get(route('admin.security.audit'));

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy');
    $response->assertHeader('Content-Security-Policy');
});

test('security audit service returns valid structure and detects settings', function () {
    $service = app(SecurityAuditService::class);
    $report = $service->runFullAudit();

    expect($report)->toBeArray()
        ->toHaveKey('compliance_score')
        ->toHaveKey('environment')
        ->toHaveKey('session')
        ->toHaveKey('routes')
        ->toHaveKey('rate_limiting')
        ->toHaveKey('password');

    expect($report['environment']['app_debug'])->toHaveKeys(['name', 'description', 'passed', 'value']);
    expect($report['session']['session_encrypt'])->toHaveKeys(['name', 'description', 'passed', 'value']);
    expect($report['password']['min_length'])->toHaveKeys(['name', 'description', 'passed', 'value']);
});

test('security audit service flags admin routes missing auth middleware as vulnerable', function () {
    // 1. Add a temporary unprotected admin route
    Route::get('/admin/temp-unsecure-test-route', function () {
        return 'unsecure';
    })->name('temp.unsecure.test');

    // 2. Add a temporary protected admin route
    Route::get('/admin/temp-secure-test-route', function () {
        return 'secure';
    })->middleware('auth')->name('temp.secure.test');

    // Refresh route lookup or just run audit
    $service = app(SecurityAuditService::class);
    $report = $service->runFullAudit();

    $vulnerableUris = collect($report['routes']['vulnerable'])->pluck('uri')->toArray();

    expect($vulnerableUris)->toContain('/admin/temp-unsecure-test-route');
    expect($vulnerableUris)->not->toContain('/admin/temp-secure-test-route');
});

test('password defaults enforces length, uppercase, and numbers', function () {
    $rules = ['password' => ['required', Password::defaults()]];

    // Too short, no uppercase, no numbers
    $validator1 = Validator::make(['password' => 'short'], $rules);
    expect($validator1->fails())->toBeTrue();

    // Long enough but no uppercase, no numbers
    $validator2 = Validator::make(['password' => 'shortbutlongenough'], $rules);
    expect($validator2->fails())->toBeTrue();

    // Long enough, uppercase, but no numbers
    $validator3 = Validator::make(['password' => 'Shortbutlongenough'], $rules);
    expect($validator3->fails())->toBeTrue();

    // Correct: 8 chars, uppercase, and number
    $validator4 = Validator::make(['password' => 'Secure123'], $rules);
    expect($validator4->fails())->toBeFalse();
});
