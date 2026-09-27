<?php

namespace Tests\Feature\Green;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreenSchoolSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_carbon_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('carbon.dashboard'));
        $response->assertStatus(200);
    }

    public function test_waste_tracker_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('waste.tracker'));
        $response->assertStatus(200);
    }

    public function test_eco_projects_index_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('eco_projects.index'));
        $response->assertStatus(200);
    }

    public function test_eco_challenges_leaderboard_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('eco_challenges.leaderboard'));
        $response->assertStatus(200);
    }
}
