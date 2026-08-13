<?php
namespace Tests\Feature\Foundation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class FoundationGovernanceTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }
    public function test_can_view_foundation_dashboard() {
        $response = $this->get('/foundation/dashboard');
        $response->assertStatus(200);
    }
    public function test_can_view_foundation_units_index() {
        $response = $this->get('/foundation/units');
        $response->assertStatus(200);
    }
    public function test_can_view_wakaf_assets_index() {
        $response = $this->get('/foundation/wakaf');
        $response->assertStatus(200);
    }
    public function test_can_view_kpi_scorecard() {
        $response = $this->get('/foundation/kpi/scorecard');
        $response->assertStatus(200);
    }
}
