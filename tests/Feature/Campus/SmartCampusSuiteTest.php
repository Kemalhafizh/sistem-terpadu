<?php

namespace Tests\Feature\Campus;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartCampusSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'admin@sultanagung1.sch.id',
        ]);
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

    public function test_canteen_menu_planner_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('canteen.menu_planner'));
        $response->assertStatus(200);
    }

    public function test_event_manager_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('events.manager'));
        $response->assertStatus(200);
    }
}
