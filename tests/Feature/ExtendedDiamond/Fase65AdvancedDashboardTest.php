<?php

namespace Tests\Feature\ExtendedDiamond;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase65AdvancedDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_command_center_returns_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($admin)->get(route('dashboards.executive.index'));

        $res->assertStatus(200);
        $res->assertSee('Pusat Komando Eksekutif');
    }

    public function test_teacher_360_dashboard_returns_200(): void
    {
        // FASE 118: it rates every teacher, so it is for the principal and the curriculum vice principal.
        $principal = User::factory()->create(['role' => 'kepala_sekolah']);
        $this->actingAs(User::factory()->create(['role' => 'guru']))->get(route('dashboards.teacher-360.index'))->assertForbidden();

        $res = $this->actingAs($principal)->get(route('dashboards.teacher-360.index'));

        $res->assertStatus(200);
        $res->assertSee('Dasbor Kinerja Guru 360 Derajat');
    }

    public function test_financial_health_scoreboard_returns_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($admin)->get(route('dashboards.financial-scoreboard.index'));

        $res->assertStatus(200);
        $res->assertSee('Papan Skor Kesehatan Keuangan');
    }

    /** FASE 118: the executive and financial dashboards are not for students or parents. */
    public function test_students_and_parents_are_refused(): void
    {
        foreach (['siswa', 'orang_tua'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (['dashboards.executive.index', 'dashboards.teacher-360.index', 'dashboards.financial-scoreboard.index'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }
}
