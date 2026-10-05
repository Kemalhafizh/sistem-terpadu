<?php

namespace Tests\Feature\HR;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedHrSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // FASE 118: these HR pages follow the hrd. group (principal and admin).
        $this->user = User::factory()->create(['role' => 'kepala_sekolah']);
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

    /** FASE 118: they used to open for any signed-in account, students and parents included. */
    public function test_other_roles_are_refused()
    {
        foreach (['siswa', 'orang_tua', 'guru', 'staf_tu'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (['recruitment.kanban', 'recruitment.vacancies', 'compensation.dashboard', 'wellness.index', 'turnover.dashboard'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }
}
