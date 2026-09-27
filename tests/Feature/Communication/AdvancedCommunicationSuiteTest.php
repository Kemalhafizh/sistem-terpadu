<?php

namespace Tests\Feature\Communication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedCommunicationSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_omnichannel_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('omnichannel.dashboard'));
        $response->assertStatus(200);
    }

    public function test_drip_campaigns_index_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('drip_campaigns.index'));
        $response->assertStatus(200);
    }

    public function test_nps_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('nps.dashboard'));
        $response->assertStatus(200);
    }

    public function test_pr_press_releases_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('pr.press_releases'));
        $response->assertStatus(200);
    }
}
