<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use App\Services\Mobile\OfflineSyncManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase77OfflineSyncProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_sync_pull_delta_api(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Device-ID', 'mobile-test-device-001')
            ->getJson('/api/v1/mobile/sync/pull?since='.urlencode(now()->subDays(7)->toIso8601String()));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'synced_at',
            'records_synced',
            'delta',
        ]);
    }

    public function test_offline_mutation_processing(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $syncManager = app(OfflineSyncManager::class);

        $mutations = [
            [
                'client_id' => 'mut_test_01',
                'type' => 'mutabaah',
                'client_timestamp' => now()->toIso8601String(),
                'payload' => [
                    'prayer' => 'subuh',
                    'is_jamaah' => true,
                ],
            ],
        ];

        $res = $syncManager->processOfflineMutations($user, 'device_s24_ultra', $mutations);
        $this->assertTrue($res['success']);
    }
}
