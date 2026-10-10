<?php

namespace Tests\Feature\Alumni;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
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

    public function test_hall_of_fame_index_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('hall_of_fame.index'));
        $response->assertStatus(200);
    }

    /** FASE 118: they used to open for any signed-in account. */
    public function test_other_roles_are_refused()
    {
        foreach (['siswa', 'orang_tua', 'guru'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (['alumni.index', 'alumni.reunionPlanner', 'hall_of_fame.index'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }

    /** FASE 119: fundraising and the endowment were cut. */
    public function test_fundraising_and_endowment_are_gone()
    {
        $this->assertFalse(Route::has('crowdfunding.index'));
        $this->assertFalse(Route::has('endowment.dashboard'));
    }
}
