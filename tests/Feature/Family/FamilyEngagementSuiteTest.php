<?php

namespace Tests\Feature\Family;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyEngagementSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** FASE 118: the homework copilot and family plan are the parents' own pages. */
    public function test_homework_copilot_dashboard_loads()
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'orang_tua']))->get(route('homework_copilot.dashboard'));
        $response->assertStatus(200);
    }

    public function test_class_feed_timeline_loads()
    {
        $response = $this->actingAs($this->admin)->get(route('class_feed.timeline'));
        $response->assertStatus(200);
    }

    public function test_family_plan_builder_loads()
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'parent']))->get(route('family_plan.builder'));
        $response->assertStatus(200);
    }

    public function test_parent_skills_directory_loads()
    {
        $response = $this->actingAs($this->admin)->get(route('parent_skills.directory'));
        $response->assertStatus(200);
    }

    /** FASE 118: students cannot open the parents' pages or post to the class feed. */
    public function test_students_are_refused()
    {
        $student = User::factory()->create(['role' => 'siswa']);
        foreach (['homework_copilot.dashboard', 'family_plan.builder', 'class_feed.timeline', 'parent_skills.directory'] as $route) {
            $this->actingAs($student)->get(route($route))->assertForbidden();
        }
    }
}
