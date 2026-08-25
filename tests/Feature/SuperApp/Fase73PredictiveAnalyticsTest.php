<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase73PredictiveAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_at_risk_student_prediction_model(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Budi Santoso']);

        $res = $this->actingAs($teacher, 'sanctum')->getJson("/api/v1/analytics/student/{$student->id}/risk");

        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $this->assertNotNull($res->json('risk_profile.composite_risk_level'));
        $this->assertNotEmpty($res->json('risk_profile.risk_factors'));
    }

    public function test_dynamic_treasury_forecasting_scenarios(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/analytics/treasury/forecast?scenario=optimistic');

        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $this->assertEquals(6, $res->json('data.forecast_count'));
    }
}
