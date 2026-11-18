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
        // treasury is for TU staff, the principal and admin.
        $this->user = User::factory()->create(['role' => 'staf_tu']);
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

    /** treasury used to open for any signed-in account, bank accounts included. */
    public function test_other_roles_are_refused()
    {
        foreach (['siswa', 'orang_tua', 'guru', 'bk'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (['treasury.accounts.index', 'treasury.reconciliation.index', 'treasury.petty-cash.index', 'treasury.position.dashboard'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }
}
