<?php

namespace Tests\Feature\DataEngineering;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataEngineeringSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin'] ?? []);
    }

    public function test_can_access_event_streams()
    {
        $this->actingAs($this->user)->get(route('data_engineering.streams'))->assertStatus(200);
    }

    public function test_can_access_nlq_search()
    {
        $this->actingAs($this->user)->get(route('nlq.search'))->assertStatus(200);
    }

    public function test_can_access_report_manager()
    {
        $this->actingAs($this->user)->get(route('report_scheduler.manager'))->assertStatus(200);
    }

    public function test_can_access_data_quality_dashboard()
    {
        $this->actingAs($this->user)->get(route('data_quality.health_dashboard'))->assertStatus(200);
    }
}
