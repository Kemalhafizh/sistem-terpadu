<?php

namespace Tests\Feature\Alumni;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumniRelationsSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // FASE 118: alumni relations are run by staff (humas, TU) and leadership.
        $this->user = User::factory()->create(['role' => 'waka_humas']);
    }

    public function test_alumni_index_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('alumni.index'));
        $response->assertStatus(200);
    }

    public function test_crowdfunding_index_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('crowdfunding.index'));
        $response->assertStatus(200);
    }

    public function test_hall_of_fame_index_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('hall_of_fame.index'));
        $response->assertStatus(200);
    }

    public function test_endowment_dashboard_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('endowment.dashboard'));
        $response->assertStatus(200);
    }

    /** FASE 118: they used to open for any signed-in account. */
    public function test_other_roles_are_refused()
    {
        foreach (['siswa', 'orang_tua', 'guru'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (['alumni.index', 'crowdfunding.index', 'hall_of_fame.index', 'endowment.dashboard'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }
}
