<?php

namespace Tests\Feature\Strategic;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategicPlanningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_view_strategic_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('strategic.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Rencana Strategis', false);
    }

    public function test_can_view_workplan_index()
    {
        $response = $this->actingAs($this->user)->get(route('strategic.workplan.index'));
        $response->assertStatus(200);
        $response->assertSee('Rencana Kerja Sekolah', false);
    }

    public function test_can_view_satisfaction_survey_index()
    {
        $response = $this->actingAs($this->user)->get(route('strategic.survey.index'));
        $response->assertStatus(200);
        $response->assertSee('Survei Kepuasan', false);
    }

    public function test_can_view_swot_analysis_index()
    {
        $response = $this->actingAs($this->user)->get(route('strategic.swot.index'));
        $response->assertStatus(200);
        $response->assertSee('Analisis SWOT', false);
    }
}
