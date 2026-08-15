<?php

namespace Tests\Feature\HR;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdvancedHrSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_recruitment_kanban_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('recruitment.kanban'));
        $response->assertStatus(200);
    }

    public function test_compensation_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('compensation.dashboard'));
        $response->assertStatus(200);
    }

    public function test_wellness_catalog_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('wellness.index'));
        $response->assertStatus(200);
    }

    public function test_turnover_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('turnover.dashboard'));
        $response->assertStatus(200);
    }
}