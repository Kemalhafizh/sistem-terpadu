<?php

use App\Models\User;
use App\Services\ObservabilityService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Save state of observability log
    $this->logPath = storage_path('logs/observability.json');
    $this->logBackup = null;
    if (File::exists($this->logPath)) {
        $this->logBackup = File::get($this->logPath);
        File::delete($this->logPath);
    }
});

afterEach(function () {
    // Restore state of observability log
    if ($this->logBackup !== null) {
        File::put($this->logPath, $this->logBackup);
    } else {
        if (File::exists($this->logPath)) {
            File::delete($this->logPath);
        }
    }
});

test('guest is redirected to login when accessing observability dashboard', function () {
    $response = $this->get(route('admin.observability.dashboard'));
    $response->assertRedirect(route('login'));
});

test('non-superadmin user is forbidden from accessing dashboard', function () {
    $user = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);

    $response = $this->actingAs($user)->get(route('admin.observability.dashboard'));
    $response->assertStatus(403);
});

test('superadmin can access dashboard and see system health title', function () {
    $user = User::factory()->create(['role' => 'superadmin', 'must_change_password' => false]);

    $response = $this->actingAs($user)->get(route('admin.observability.dashboard'));
    $response->assertOk();
    $response->assertSee('Observabilitas &amp; Kesehatan Sistem', false);
});

test('superadmin can retrieve metrics JSON payload', function () {
    $user = User::factory()->create(['role' => 'superadmin', 'must_change_password' => false]);

    $response = $this->actingAs($user)->get(route('admin.observability.metrics'));
    $response->assertOk();
    $response->assertJsonStructure([
        'cpu',
        'ram' => ['used', 'total', 'percentage'],
        'disk' => ['used', 'total', 'percentage'],
        'db_size',
        'active_users',
        'queue_depth'
    ]);
});

test('middleware logs performance records on slow requests', function () {
    $user = User::factory()->create(['role' => 'superadmin', 'must_change_password' => false]);

    // Ensure the log doesn't exist
    if (File::exists($this->logPath)) {
        File::delete($this->logPath);
    }

    // Trigger simulation endpoint which triggers a 1.5s sleep in PerformanceLogger
    $response = $this->actingAs($user)->post(route('admin.observability.simulate'), [
        'type' => 'request'
    ]);

    $response->assertRedirect();
    
    // Verify file was written
    $this->assertTrue(File::exists($this->logPath));
    $logContent = File::get($this->logPath);
    $this->assertStringContainsString('"type":"performance"', $logContent);
    $this->assertStringContainsString('"duration_ms":', $logContent);
});

test('superadmin can clear observability logs', function () {
    $user = User::factory()->create(['role' => 'superadmin', 'must_change_password' => false]);

    // Create a mock log entry
    File::put($this->logPath, json_encode(['timestamp' => now()->toIso8601String(), 'type' => 'performance']) . "\n");

    $response = $this->actingAs($user)->post(route('admin.observability.clear-logs'));
    $response->assertRedirect();
    
    $this->assertFalse(File::exists($this->logPath));
});
