<?php

namespace Tests\Feature\Safety;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSafetySuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_cctv_index_returns_200()
    {
        $response = $this->get(route('cctv.index'));
        $response->assertStatus(200);
    }

    public function test_emergency_alerts_index_returns_200()
    {
        $response = $this->get(route('emergency_alerts.index'));
        $response->assertStatus(200);
    }

    public function test_safety_drills_index_returns_200()
    {
        $response = $this->get(route('safety_drills.index'));
        $response->assertStatus(200);
    }

    public function test_geofencing_index_returns_200()
    {
        $response = $this->get(route('geofencing.index'));
        $response->assertStatus(200);
    }
}
