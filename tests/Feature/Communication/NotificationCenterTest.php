<?php

use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Communication\NotificationDispatchService;
use Tests\TestCase;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->dispatchService = app(NotificationDispatchService::class);
});

test('notification service dispatches multi-channel notification according to user preferences', function () {
    $logs = $this->dispatchService->dispatch(
        $this->user->id,
        'academic.grade_published',
        'Evaluasi Rapor Terbit',
        'Nilai semester genap telah terbit secara resmi.',
        ['semester' => 2]
    );

    expect($logs)->not()->toBeEmpty();
    expect(NotificationLog::where('user_id', $this->user->id)->count())->toBeGreaterThanOrEqual(1);

    $log = NotificationLog::where('user_id', $this->user->id)->first();
    expect($log->title)->toBe('Evaluasi Rapor Terbit');
    expect($log->event_category)->toBe('academic');
});

test('user can view enterprise notification inbox and unread counter', function () {
    $this->dispatchService->dispatch(
        $this->user->id,
        'finance.spp_due',
        'Tagihan SPP Juli',
        'Tagihan SPP telah terbit.'
    );

    $response = $this->actingAs($this->user)->get(route('notifications.index'));
    $response->assertStatus(200);
    $response->assertSee('Tagihan SPP Juli');

    $countResponse = $this->actingAs($this->user)->get(route('notifications.unread_count'));
    $countResponse->assertStatus(200);
    $countResponse->assertJson(['unread_count' => 1]);
});

test('user can mark notification as read and mark all read', function () {
    $logs = $this->dispatchService->dispatch(
        $this->user->id,
        'system.alert',
        'Alert Keamanan',
        'Deteksi login perangkat baru.'
    );

    $notifId = $logs[0]->id;

    $response = $this->actingAs($this->user)->post(route('notifications.read', $notifId));
    $response->assertRedirect();

    $log = NotificationLog::find($notifId);
    expect($log->isRead())->toBeTrue();

    // Test mark all read
    $this->dispatchService->dispatch($this->user->id, 'system.alert', 'Pesan 2', 'Pesan 2 body');
    $readAllResponse = $this->actingAs($this->user)->post(route('notifications.read_all'));
    $readAllResponse->assertRedirect();

    expect($this->dispatchService->getUnreadCount($this->user->id))->toBe(0);
});

test('user can view and update channel preferences per category', function () {
    $prefResponse = $this->actingAs($this->user)->get(route('notifications.preferences'));
    $prefResponse->assertStatus(200);

    $updateResponse = $this->actingAs($this->user)->post(route('notifications.preferences.update'), [
        'preferences' => [
            'academic' => ['inapp' => '1', 'email' => '1', 'whatsapp' => '1'],
            'finance' => ['inapp' => '1', 'email' => '1'],
        ],
    ]);

    $updateResponse->assertRedirect();

    $prefs = $this->dispatchService->getUserPreferences($this->user->id)->keyBy('event_category');
    expect($prefs['academic']->channel_whatsapp)->toBeTrue();
});

test('admin can trigger test notification dispatch', function () {
    $testResponse = $this->actingAs($this->user)->post(route('notifications.test_dispatch'), [
        'event_type' => 'academic.grade_published',
        'title' => 'Test Dispatch Title',
        'body' => 'Test Dispatch Body',
    ]);

    $testResponse->assertRedirect();
    expect(NotificationLog::where('user_id', $this->user->id)->where('title', 'Test Dispatch Title')->exists())->toBeTrue();
});
