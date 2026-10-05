<?php

namespace Tests\Feature\Campus;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SmartCampusSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // FASE 118: campus operations are for TU staff, sarpras, the principal and admin.
        $this->user = User::factory()->create(['role' => 'staf_tu']);
    }

    public function test_visitor_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('visitors.dashboard'));
        $response->assertStatus(200);
    }

    public function test_store_catalog_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('store.catalog'));
        $response->assertStatus(200);
    }

    /**
     * FASE 118: the canteen is run by outside vendors who rent a stall, not by
     * school staff, so its planner left the ERP (this test used to expect 200).
     */
    public function test_canteen_planner_is_gone()
    {
        $this->assertFalse(Route::has('canteen.menu_planner'));
        $this->assertFalse(Route::has('canteen.nutrition_tracker'));
    }

    public function test_event_manager_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('events.manager'));
        $response->assertStatus(200);
    }

    /** FASE 118: campus operations used to open for any signed-in account. */
    public function test_other_roles_are_refused()
    {
        foreach (['siswa', 'orang_tua', 'guru'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (['visitors.dashboard', 'store.catalog', 'events.manager'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }
}
