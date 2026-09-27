<?php

namespace Tests\Feature\Inclusive;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InclusiveEducationSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
    }

    public function test_iep_index_returns_200()
    {
        $response = $this->get(route('iep.index'));
        $response->assertStatus(200);
    }

    public function test_screening_dashboard_returns_200()
    {
        $response = $this->get(route('screening.dashboard'));
        $response->assertStatus(200);
    }

    public function test_differentiation_library_returns_200()
    {
        $response = $this->get(route('differentiation.library'));
        $response->assertStatus(200);
    }

    public function test_accommodations_registry_returns_200()
    {
        $response = $this->get(route('accommodations.registry'));
        $response->assertStatus(200);
    }
}
