<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase84SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_devices_listing_and_revocation(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        UserDeviceToken::create([
            'user_id' => $user->id,
            'device_id' => 'dev_sample_123',
            'device_name' => 'Pixel 9 Pro',
            'platform' => 'android',
            'fcm_token' => 'mock_fcm_token_123',
            'is_active' => true,
        ]);

        $listRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/mobile/devices');
        $listRes->assertStatus(200);
        $listRes->assertJsonCount(1, 'devices');

        $revokeRes = $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/mobile/devices/dev_sample_123');
        $revokeRes->assertStatus(200);
        $revokeRes->assertJson(['success' => true]);
    }

    public function test_unauthenticated_requests_are_blocked(): void
    {
        $response = $this->getJson('/api/v1/student/schedule');
        $response->assertStatus(401);
    }
}
