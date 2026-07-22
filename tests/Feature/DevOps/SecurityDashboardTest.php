<?php

use App\Models\BlockedIp;
use App\Models\SecurityThreatLog;
use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
    Setting::set('is_system_lockdown', '0');
});

test('admin can view security dashboard and threat logs', function () {
    $responseDashboard = $this->actingAs($this->admin)->get(route('admin.security.index'));
    $responseDashboard->assertStatus(200);
    $responseDashboard->assertSee('Security Forensic Log');

    $responseLogs = $this->actingAs($this->admin)->get(route('admin.security.logs'));
    $responseLogs->assertStatus(200);
    $responseLogs->assertSee('Forensic Audit Trail');
});

test('admin can toggle emergency system lockdown status', function () {
    expect((bool) Setting::get('is_system_lockdown', false))->toBeFalse();

    // Enable lockdown
    $responseEnable = $this->actingAs($this->admin)->post(route('admin.security.lockdown'));
    $responseEnable->assertStatus(302);
    $responseEnable->assertRedirect(route('admin.security.index'));
    expect((bool) Setting::get('is_system_lockdown', false))->toBeTrue();

    // Disable lockdown
    $responseDisable = $this->actingAs($this->admin)->post(route('admin.security.lockdown'));
    $responseDisable->assertStatus(302);
    expect((bool) Setting::get('is_system_lockdown', false))->toBeFalse();
});

test('system lockdown middleware blocks regular users when enabled', function () {
    Setting::set('is_system_lockdown', '1');

    // Student trying to access dashboard should get 503
    $responseStudent = $this->actingAs($this->student)->get(route('dashboard'));
    $responseStudent->assertStatus(503);

    // Admin accessing security dashboard should still pass
    $responseAdmin = $this->actingAs($this->admin)->get(route('admin.security.index'));
    $responseAdmin->assertStatus(200);
});

test('admin can manually block and unblock ip address', function () {
    $responseBlock = $this->actingAs($this->admin)->post(route('admin.security.block-ip'), [
        'ip_address' => '198.51.100.99',
        'reason' => 'Test Manual Block IP',
    ]);
    $responseBlock->assertStatus(302);

    $this->assertDatabaseHas('blocked_ips', [
        'ip_address' => '198.51.100.99',
        'reason' => 'Test Manual Block IP',
    ]);

    $blocked = BlockedIp::where('ip_address', '198.51.100.99')->first();
    $responseUnblock = $this->actingAs($this->admin)->delete(route('admin.security.unblock-ip', $blocked->id));
    $responseUnblock->assertStatus(302);

    $this->assertDatabaseMissing('blocked_ips', [
        'ip_address' => '198.51.100.99',
    ]);
});

test('system lockdown middleware blocks banned IP addresses with 403', function () {
    BlockedIp::create([
        'ip_address' => '127.0.0.1',
        'reason' => 'Banned Local IP',
    ]);

    $response = $this->actingAs($this->admin)->get(route('dashboard'));
    $response->assertStatus(403);
});

test('admin can mark threat log as resolved', function () {
    $threat = SecurityThreatLog::create([
        'user_id' => $this->admin->id,
        'event_type' => 'test_brute_force',
        'severity' => 'critical',
        'ip_address' => '10.0.0.1',
        'is_resolved' => false,
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.security.resolve-threat', $threat->id));
    $response->assertStatus(302);

    $threat->refresh();
    expect($threat->is_resolved)->toBeTrue();
});
