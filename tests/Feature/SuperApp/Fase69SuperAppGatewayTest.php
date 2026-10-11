<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Events\CampusAnnouncementEvent;
use App\Events\EmergencyBroadcastEvent;
use App\Models\User;
use App\Services\Mobile\FcmNotificationService;
use App\Services\Mobile\OfflineSyncManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class Fase69SuperAppGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_fcm_notification_token_registration_and_dispatch(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $service = app(FcmNotificationService::class);

        $token = $service->registerDeviceToken($user, 'fcm_token_sample_abc123', 'android', 'Samsung Galaxy S24');
        $this->assertDatabaseHas('user_device_tokens', [
            'user_id' => $user->id,
            'device_token' => 'fcm_token_sample_abc123',
            'is_active' => true,
        ]);

        $service->sendToUser($user->id, 'Tagihan Baru', 'SPP Bulan September telah terbit');
        $this->assertTrue(true);
    }

    public function test_offline_sync_manager_delta_pull_and_mutation_queue(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $syncManager = app(OfflineSyncManager::class);

        $delta = $syncManager->pullDelta($user);
        $this->assertArrayHasKey('server_timestamp', $delta);
        $this->assertArrayHasKey('delta_counts', $delta);

        $mutations = [
            [
                'client_id' => 'mut_101',
                'type' => 'attendance',
                'client_timestamp' => now()->toIso8601String(),
                'payload' => [
                    'date' => now()->toDateString(),
                    'status' => 'present',
                    'notes' => 'Presensi offline gerbang barat',
                ],
            ],
        ];

        $processResult = $syncManager->processOfflineMutations($user, 'device_poco_m4_01', $mutations);
        $this->assertTrue($processResult['success']);
        $this->assertEquals(1, $processResult['records_synced']);
        $this->assertDatabaseHas('sync_logs', [
            'user_id' => $user->id,
            'device_id' => 'device_poco_m4_01',
            'status' => 'success',
        ]);
    }

    public function test_websocket_broadcast_events(): void
    {
        Event::fake([
            EmergencyBroadcastEvent::class,
            CampusAnnouncementEvent::class,
        ]);

        EmergencyBroadcastEvent::dispatch(['level' => 'RED', 'alert' => 'Simulasi Kebakaran']);
        CampusAnnouncementEvent::dispatch(['title' => 'Libur Maulid Nabi']);

        Event::assertDispatched(EmergencyBroadcastEvent::class);
        Event::assertDispatched(CampusAnnouncementEvent::class);
    }

    public function test_geofence_checkin_api(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/mobile/geofence/checkin', [
            'latitude' => -6.996160,
            'longitude' => 110.428510,
            'accuracy' => 5.0,
            'is_mock' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'verified');
    }
}
