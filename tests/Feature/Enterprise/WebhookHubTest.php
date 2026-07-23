<?php

use App\Models\User;
use App\Models\WebhookDelivery;
use App\Services\WebhookManagerService;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->webhookManager = app(WebhookManagerService::class);
});

test('webhook manager registers subscription and dispatches events with hmac signature', function () {
    $sub = $this->webhookManager->registerSubscription([
        'name' => 'Unit Test System',
        'target_url' => 'https://example.com/webhook',
        'secret_key' => 'whsec_test_secret_123',
        'events' => ['student.registered'],
        'is_active' => true,
    ]);

    expect($sub->name)->toBe('Unit Test System');
    expect($sub->secret_key)->toBe('whsec_test_secret_123');

    $dispatchRes = $this->webhookManager->dispatch('student.registered', ['id' => 99, 'name' => 'Ahmad']);

    expect($dispatchRes['dispatched_count'])->toBe(1);
    expect($dispatchRes['deliveries'])->toHaveCount(1);
    expect($dispatchRes['deliveries'][0]->signature)->not()->toBeEmpty();
});

test('webhook manager replays failed delivery attempt', function () {
    $sub = $this->webhookManager->registerSubscription([
        'name' => 'Replay Test',
        'target_url' => 'https://example.com/webhook',
        'secret_key' => 'whsec_replay_secret',
        'events' => ['billing.paid'],
    ]);

    $delivery = $this->webhookManager->deliver($sub, 'billing.paid', ['amount' => 500000]);
    $replayed = $this->webhookManager->replayFailedDelivery($delivery->id);

    expect($replayed->attempt_count)->toBe(2);
});

test('admin can view webhook hub console', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.webhooks.index'));
    $response->assertStatus(200);
    $response->assertSee('Real-Time Event Streaming Bus');
    $response->assertSee('HMAC-SHA256');
});

test('admin can store new webhook subscription via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.webhooks.index'))
        ->post(route('admin.webhooks.store'), [
            'name' => 'Kemenag Integration',
            'target_url' => 'https://kemenag.go.id/webhook',
            'events' => ['grade.published'],
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.webhooks.index'));
});

test('admin can dispatch test event via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.webhooks.index'))
        ->post(route('admin.webhooks.test-dispatch'), [
            'event_type' => 'student.registered',
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.webhooks.index'));
});
