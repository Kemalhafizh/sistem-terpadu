<?php

use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Models\WorkflowApproval;
use App\Models\Student;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'name' => 'Admin Utama',
    ]);

    $this->waka = User::factory()->create([
        'role' => 'waka_kesiswaan',
        'name' => 'Waka Kesiswaan',
    ]);

    $this->definition = WorkflowDefinition::create([
        'name' => 'Pengajuan Kegiatan OSIS',
        'code' => 'OSIS_ACT',
        'description' => 'Workflow untuk persetujuan kegiatan.',
        'steps' => [
            ['order' => 1, 'role' => 'admin', 'sla_hours' => 24]
        ],
        'is_active' => true,
    ]);

    $this->student = Student::factory()->create();

    // Create a workflow request referencing the student as requestable
    $this->workflowRequest = WorkflowRequest::create([
        'definition_id' => $this->definition->id,
        'requester_id' => $this->admin->id,
        'requestable_type' => Student::class,
        'requestable_id' => $this->student->id,
        'current_step' => 1,
        'status' => 'pending',
        'notes' => 'Catatan pengajuan',
        'due_at' => now()->addHours(24),
    ]);

    // Create the active approval step matching admin role
    $this->approval = WorkflowApproval::create([
        'request_id' => $this->workflowRequest->id,
        'step_order' => 1,
        'role' => 'admin',
        'decision' => 'pending',
        'due_at' => now()->addHours(24),
    ]);
});

test('admin can delegate pending approval step to another user', function () {
    $response = $this->actingAs($this->admin)->post(route('workflows.delegate', $this->workflowRequest), [
        'delegatee_id' => $this->waka->id,
        'remarks' => 'Tolong dibantu verifikasi berkas ini.',
    ]);

    $response->assertRedirect(route('workflows.pending'));
    
    $this->approval->refresh();
    expect($this->approval->delegated_to_id)->toBe($this->waka->id);
    expect($this->approval->remarks)->toContain('Didelegasikan dari Admin Utama ke Waka Kesiswaan');
});
