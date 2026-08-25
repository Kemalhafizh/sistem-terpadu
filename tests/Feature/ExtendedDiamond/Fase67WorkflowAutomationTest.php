<?php

namespace Tests\Feature\ExtendedDiamond;

use App\Models\ApprovalChain;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Models\WorkflowBlueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase67WorkflowAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_designer_and_approval_chain_decision(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guru = User::factory()->create(['role' => 'guru']);

        $res = $this->actingAs($admin)->get(route('workflow.designer.index'));
        $res->assertStatus(200);
        $res->assertSee('Desainer Alur Kerja Visual');

        $blueprint = WorkflowBlueprint::create([
            'name' => 'Pengadaan Sarpras Laboratorium',
            'process_type' => 'procurement',
            'bpmn_schema' => ['nodes' => []],
            'created_by' => $admin->id,
        ]);

        $chain = ApprovalChain::create([
            'workflow_blueprint_id' => $blueprint->id,
            'current_node_key' => 'review_kepsek',
            'status' => 'in_progress',
            'requester_id' => $guru->id,
        ]);

        $request = ApprovalRequest::create([
            'approval_chain_id' => $chain->id,
            'approver_id' => $admin->id,
            'node_key' => 'review_kepsek',
            'decision' => 'pending',
        ]);

        $decisionRes = $this->actingAs($admin)->postJson(route('workflow.approvals.decision', $request->id), [
            'decision' => 'approved',
            'comments' => 'Disetujui untuk peningkatan fasilitas lab sains.',
        ]);

        $decisionRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('approval_chains', [
            'id' => $chain->id,
            'status' => 'approved',
        ]);
    }
}
