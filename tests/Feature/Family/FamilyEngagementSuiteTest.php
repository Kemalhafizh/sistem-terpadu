<?php

namespace Tests\Feature\Family;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Employee;

class FamilyEngagementSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_homework_copilot_dashboard_loads()
    {
        $response = $this->actingAs($this->admin)->get(route('homework_copilot.dashboard'));
        $response->assertStatus(200);
    }

    public function test_class_feed_timeline_loads()
    {
        $response = $this->actingAs($this->admin)->get(route('class_feed.timeline'));
        $response->assertStatus(200);
    }

    public function test_family_plan_builder_loads()
    {
        $response = $this->actingAs($this->admin)->get(route('family_plan.builder'));
        $response->assertStatus(200);
    }

    public function test_parent_skills_directory_loads()
    {
        $response = $this->actingAs($this->admin)->get(route('parent_skills.directory'));
        $response->assertStatus(200);
    }
}
