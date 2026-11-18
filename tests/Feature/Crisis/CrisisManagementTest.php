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
        // crisis management is for leadership; the bullying form for everyone.
        $this->user = User::factory()->create(['role' => 'kepala_sekolah']);
    }

    public function test_can_view_bullying_report_form()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.bullying.report'));
        $response->assertStatus(200);
        $response->assertSee('Laporan');
    }

    /** anyone signed in may report bullying, but not read the reports. */
    public function test_students_and_parents_report_bullying_but_cannot_read_reports()
    {
        foreach (['siswa', 'orang_tua'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route('crisis.bullying.report'))->assertOk();
            foreach (['crisis.bullying.index', 'crisis.bullying.statistics'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }
}
