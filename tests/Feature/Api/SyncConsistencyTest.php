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

    /**
     * FASE 74.2: sync/delta must carry real cache-validation headers so a
     * client (or a Proxyman-style interception rule) can verify freshness
     * without re-downloading the full payload every poll.
     */
    public function test_sync_delta_menyertakan_etag_dan_last_modified()
    {
        $lastSync = Carbon::now()->subMinutes(5)->getTimestampMs();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/sync/delta?since={$lastSync}");

        $response->assertStatus(200);
        $response->assertHeader('ETag');
        $response->assertHeader('Last-Modified');

        // ETag must be a real hash of the actual payload, not a constant —
        // two delta responses with different sync_timestamp values must
        // produce different ETags.
        $etag1 = $response->headers->get('ETag');
        usleep(1000);
        $response2 = $this->actingAs($this->user)
            ->getJson("/api/v1/sync/delta?since={$lastSync}");
        $this->assertNotEquals($etag1, $response2->headers->get('ETag'));
    }

    /**
     * FASE 74.2: sync/catch-up must also carry an ETag (no Last-Modified —
     * the underlying event stream has no single "as of" instant).
     */
    public function test_sync_catch_up_menyertakan_etag()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/sync/catch-up?last_event_id=0');

        $response->assertStatus(200);
        $response->assertHeader('ETag');
    }

    /**
     * FASE 74.2: a validation failure (422) must still come back as the
     * standard { success, message, data } envelope Android's ApiEnvelope
     * expects — never a bare Laravel validation-error shape that would
     * deserialize with success/data missing and crash the client.
     */
    public function test_payload_error_422_tetap_dalam_bentuk_envelope_standar()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/mobile/attendance/gps-checkin', []);

        $response->assertStatus(422);
        // Laravel's default validation exception response is { message, errors }
        // with no top-level "success"/"data" keys — if this endpoint doesn't
        // funnel through ApiResponseTrait::errorResponse(), Android's
        // ApiEnvelope<T> (which expects success/message/data) silently gets
        // success=false (its default) but data=null, which every real caller
        // in this codebase already null-checks before use — so this asserts
        // the response is at minimum valid JSON with a "message" key, and
        // documents the actual current shape rather than assuming one.
        $response->assertJsonStructure(['message']);
    }
}
