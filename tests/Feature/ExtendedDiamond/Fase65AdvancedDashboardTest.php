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
        $teacher = User::factory()->create(['role' => 'guru']);

        $res = $this->actingAs($teacher)->get(route('dashboards.teacher-360.index'));

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
}
