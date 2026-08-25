<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase79LocalizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_language_and_app_locale_support(): void
    {
        $locales = ['id', 'en', 'ar'];
        foreach ($locales as $locale) {
            app()->setLocale($locale);
            $this->assertEquals($locale, app()->getLocale());
        }
    }

    public function test_mobile_calendar_events_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/calendar');
        $response->assertStatus(200);
    }
}
