<?php
namespace Tests\Feature\Islamic;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class IslamicTechSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_tahsin_index_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('tahsin.index'));
        $response->assertStatus(200);
    }

    public function test_islamic_events_calendar_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('islamic_events.calendar'));
        $response->assertStatus(200);
    }

    public function test_spiritual_dashboard_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('spiritual.dashboard'));
        $response->assertStatus(200);
    }

    public function test_dawah_hub_returns_200()
    {
        $response = $this->actingAs($this->user)->get(route('dawah.hub'));
        $response->assertStatus(200);
    }
}