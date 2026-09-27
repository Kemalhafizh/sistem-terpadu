<?php

namespace Tests\Feature\Treasury;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasuryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_view_bank_accounts_index()
    {
        $response = $this->get('/treasury/accounts');
        $response->assertStatus(200);
    }

    public function test_can_view_reconciliation_index()
    {
        $response = $this->get('/treasury/reconciliation');
        $response->assertStatus(200);
    }

    public function test_can_view_petty_cash_index()
    {
        $response = $this->get('/treasury/petty-cash');
        $response->assertStatus(200);
    }

    public function test_can_view_treasury_position_dashboard()
    {
        $response = $this->get('/treasury/position');
        $response->assertStatus(200);
    }
}
