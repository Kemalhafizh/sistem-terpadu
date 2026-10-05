<?php

namespace Tests\Feature\Crisis;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrisisManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // FASE 118: crisis management is for leadership; the bullying form for everyone.
        $this->user = User::factory()->create(['role' => 'kepala_sekolah']);
    }

    public function test_can_view_crisis_command_center()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.command_center'));
        $response->assertStatus(200);
        $response->assertSee('Emergency Command Center');
    }

    public function test_can_view_crisis_protocols()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.protocols.index'));
        $response->assertStatus(200);
        $response->assertSee('Protokol');
    }

    public function test_can_view_emergency_broadcast_index()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.broadcast.index'));
        $response->assertStatus(200);
        $response->assertSee('Emergency Broadcast');
    }

    public function test_can_view_bullying_report_form()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.bullying.report'));
        $response->assertStatus(200);
        $response->assertSee('Laporan');
    }

    /** FASE 118: anyone signed in may report bullying, but not run the crisis center. */
    public function test_students_and_parents_report_bullying_but_cannot_run_crisis_pages()
    {
        foreach (['siswa', 'orang_tua'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('crisis.bullying.report'))->assertOk();
            foreach (['crisis.command_center', 'crisis.protocols.index', 'crisis.broadcast.index', 'crisis.bullying.index', 'crisis.bullying.statistics'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }
}
