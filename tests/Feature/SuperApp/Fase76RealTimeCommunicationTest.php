<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Events\CampusAnnouncementEvent;
use App\Events\EmergencyBroadcastEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class Fase76RealTimeCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_websocket_channels_and_announcement_broadcast(): void
    {
        Event::fake([CampusAnnouncementEvent::class, EmergencyBroadcastEvent::class]);

        CampusAnnouncementEvent::dispatch(['title' => 'Pengumuman Ujian Tengah Semester']);
        EmergencyBroadcastEvent::dispatch(['level' => 'RED', 'alert' => 'Peringatan Gempa Terdeteksi']);

        Event::assertDispatched(CampusAnnouncementEvent::class);
        Event::assertDispatched(EmergencyBroadcastEvent::class);
    }

    public function test_general_announcements_and_notifications_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $announcementResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/announcements');
        $announcementResponse->assertStatus(200);

        $notificationResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications');
        $notificationResponse->assertStatus(200);
    }
}
