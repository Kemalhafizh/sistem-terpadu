<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_submit_ganda_dengan_idempotency_key_yang_sama()
    {
        $idempotencyKey = Str::uuid()->toString();

        $payload = [
            'actions' => [
                [
                    'id' => 'action-1',
                    'type' => 'offline_draft',
                    'data' => ['title' => 'Test']
                ]
            ]
        ];

        // 1st request
        $response1 = $this->actingAs($this->user)
            ->postJson('/api/v1/sync/batch', $payload, [
                'Idempotency-Key' => $idempotencyKey
            ]);
        
        $response1->assertStatus(200);

        // Modify payload just to prove it won't be processed but returned from cache
        $payload2 = [
            'actions' => [
                [
                    'id' => 'action-2',
                    'type' => 'offline_draft',
                    'data' => ['title' => 'Test Modified']
                ]
            ]
        ];

        // 2nd request with SAME idempotency key
        $response2 = $this->actingAs($this->user)
            ->postJson('/api/v1/sync/batch', $payload2, [
                'Idempotency-Key' => $idempotencyKey
            ]);

        $response2->assertStatus(200);
        
        // Response content should match EXACTLY the first response because it's cached
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    public function test_tombstone_sync_soft_deleted_records()
    {
        // Add one announcement
        $announcement = Announcement::create([
            'title' => 'Pengumuman 1',
            'content' => 'Isi',
            'created_by' => $this->user->id,
            'is_active' => true,
        ]);

        $lastSync = Carbon::now()->subMinutes(5)->getTimestampMs();

        // Soft delete it
        $announcement->delete();

        // Query delta
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/sync/delta?since={$lastSync}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'is_deleted' => true,
                'type' => 'announcement',
            ]);
    }

    public function test_resolusi_milidetik_delta_sync()
    {
        // Emulate precision sync
        $lastSync = Carbon::now()->subMinutes(5)->getTimestampMs();
        
        // This is a simulated test ensuring the endpoint handles millisecond timestamps without breaking
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/sync/delta?since={$lastSync}");
        
        $response->assertStatus(200);
        $this->assertGreaterThan(9999999999, $response->json('data.sync_timestamp'));
    }

    public function test_catch_up_log_redis_streams()
    {
        // Mock catch-up returning dummy values in service
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/sync/catch-up?last_event_id=1700000000-0');

        $response->assertStatus(200)
            ->assertJsonPath('data.events.0.type', 'chat_message')
            ->assertJsonPath('data.events.1.type', 'point_update')
            ->assertJsonPath('data.last_event_id', '1700000002-0');
    }
}
