<?php

namespace Tests\Feature\Operations;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_view_iot_facility_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('facility.iot.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('IoT Smart Facility', false);
    }

    public function test_can_view_utility_tracker_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('facility.utility.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Utility Expense', false);
    }

    public function test_can_view_transportation_routes()
    {
        $response = $this->actingAs($this->user)->get(route('facility.transport.routes'));
        $response->assertStatus(200);
        $response->assertSee('Transportation & Bus Routing System', false);
    }

    public function test_can_view_asset_maintenance_schedule()
    {
        $response = $this->actingAs($this->user)->get(route('facility.maintenance.schedule'));
        $response->assertStatus(200);
        $response->assertSee('Asset Maintenance', false);
    }
}
