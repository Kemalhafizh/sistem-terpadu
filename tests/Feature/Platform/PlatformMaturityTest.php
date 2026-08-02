<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformMaturityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_view_developer_api_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('developer.api.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Developer API Gateway', false);
    }

    public function test_can_view_dashboard_builder_index()
    {
        $response = $this->actingAs($this->user)->get(route('developer.dashboards.index'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Builder', false);
    }

    public function test_can_view_ai_recommendations()
    {
        $response = $this->actingAs($this->user)->get(route('developer.ai.recommendations'));
        $response->assertStatus(200);
        $response->assertSee('AI Recommendation', false);
    }

    public function test_can_view_digital_twin_simulation()
    {
        $response = $this->actingAs($this->user)->get(route('developer.twin.simulation'));
        $response->assertStatus(200);
        $response->assertSee('Digital Twin School Infrastructure', false);
    }
}
