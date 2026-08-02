<?php

namespace Tests\Feature\Compliance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceRegulatoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_view_dapodik_export_index()
    {
        $response = $this->actingAs($this->user)->get(route('compliance.dapodik.index'));
        $response->assertStatus(200);
        $response->assertSee('Dapodik/EMIS Auto-Export', false);
    }

    public function test_can_view_data_retention_index()
    {
        $response = $this->actingAs($this->user)->get(route('compliance.retention.index'));
        $response->assertStatus(200);
        $response->assertSee('Automated Data Retention', false);
    }

    public function test_can_view_audit_console()
    {
        $response = $this->actingAs($this->user)->get(route('compliance.audit.console'));
        $response->assertStatus(200);
        $response->assertSee('Digital Forensics & Compliance Audit Console', false);
    }

    public function test_can_view_legal_contracts_index()
    {
        $response = $this->actingAs($this->user)->get(route('compliance.contracts.index'));
        $response->assertStatus(200);
        $response->assertSee('Legal Contract & Vendor Compliance Manager', false);
    }
}
