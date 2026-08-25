<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase81WidgetDeepLinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_schedule_and_today_classes_for_widget(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/student/schedule');
        $response->assertStatus(200);
    }
}
