<?php

namespace Tests\Feature\Crisis;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CrisisManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_view_crisis_command_center()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.command_center'));
        $response->assertStatus(200);
        $response->assertSee('Emergency Command Center');
    }

    public function test_can_view_crisis_protocols()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.protocols.index'));
        $response->assertStatus(200);
        $response->assertSee('Protokol');
    }

    public function test_can_view_emergency_broadcast_index()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.broadcast.index'));
        $response->assertStatus(200);
        $response->assertSee('Emergency Broadcast');
    }

    public function test_can_view_bullying_report_form()
    {
        $response = $this->actingAs($this->user)->get(route('crisis.bullying.report'));
        $response->assertStatus(200);
        $response->assertSee('Laporan');
    }
}