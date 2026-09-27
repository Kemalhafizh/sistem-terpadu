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
        $this->user = User::factory()->create();
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
}
