<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Services\WorkflowService;

beforeEach(function () {
    // Create users with roles
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->wakaKur = User::factory()->create(['role' => 'waka_kurikulum']);
    $this->guru = User::factory()->create(['role' => 'guru']);

    // Create employee for leave requests
    $this->employee = Employee::create([
        'user_id' => $this->guru->id,
        'employee_number' => 'EMP123',
        'position' => 'Guru',
        'employment_status' => 'gty',
        'hire_date' => now(),
    ]);

    // Create workflow definition
    $this->definition = WorkflowDefinition::create([
        'name' => 'Persetujuan Cuti Tahunan',
        'code' => 'leave_annual_approval',
        'description' => 'Alur persetujuan cuti tahunan guru dan staf.',
        'steps' => [
            ['order' => 1, 'role' => 'waka_kurikulum', 'sla_hours' => 24],
            ['order' => 2, 'role' => 'kepala_sekolah', 'sla_hours' => 48],
        ],
        'is_active' => true,
    ]);
});

// ── 1. ROLE-BASED ACCESS CONTROL (RBAC) ─────────────────────────────────

test('authorized users can access workflows dashboard, definitions, and analytics', function () {
    $this->actingAs($this->admin)->get(route('workflows.dashboard'))->assertStatus(200);
    $this->actingAs($this->admin)->get(route('workflows.definitions'))->assertStatus(200);
    $this->actingAs($this->admin)->get(route('workflows.analytics'))->assertStatus(200);

    $this->actingAs($this->kepsek)->get(route('workflows.dashboard'))->assertStatus(200);
});

test('unauthorized users are forbidden from access workflow admin panels', function () {
    $this->actingAs($this->guru)->get(route('workflows.dashboard'))->assertStatus(403);
    $this->actingAs($this->guru)->get(route('workflows.definitions'))->assertStatus(403);
    $this->actingAs($this->guru)->get(route('workflows.analytics'))->assertStatus(403);
});

// ── 2. DEFINITION CRUD ──────────────────────────────────────────────────

test('admin can manage workflow definitions', function () {
    // 1. Create
    $response = $this->actingAs($this->admin)->post(route('workflows.definitions.store'), [
        'name' => 'Alur Baru',
        'code' => 'new_workflow_code',
        'description' => 'Deskripsi baru.',
        'steps' => [
            ['order' => 1, 'role' => 'bk', 'sla_hours' => 12],
            ['order' => 2, 'role' => 'admin', 'sla_hours' => 24],
        ],
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('workflows.definitions'));
    $this->assertDatabaseHas('workflow_definitions', [
        'name' => 'Alur Baru',
        'code' => 'new_workflow_code',
    ]);

    $def = WorkflowDefinition::where('code', 'new_workflow_code')->first();
    expect($def->steps)->toBe([
        ['order' => 1, 'role' => 'bk', 'sla_hours' => 12],
        ['order' => 2, 'role' => 'admin', 'sla_hours' => 24],
    ]);

    // 2. Update
    $response = $this->actingAs($this->admin)->put(route('workflows.definitions.update', $def->id), [
        'name' => 'Alur Baru Diedit',
        'description' => 'Deskripsi diedit.',
        'steps' => [
            ['order' => 1, 'role' => 'staf_tu', 'sla_hours' => 48],
        ],
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('workflows.definitions'));
    $this->assertDatabaseHas('workflow_definitions', [
        'id' => $def->id,
        'name' => 'Alur Baru Diedit',
    ]);

    // 3. Delete
    $response = $this->actingAs($this->admin)->delete(route('workflows.definitions.delete', $def->id));
    $response->assertRedirect(route('workflows.definitions'));
    $this->assertDatabaseMissing('workflow_definitions', ['id' => $def->id]);
});

// ── 3. REVISE & RESUBMIT FLOW ─────────────────────────────────────────

test('approver can request revision and requester can resubmit request', function () {
    // 1. Create a Leave Request
    $leave = LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(5),
        'type' => 'cuti_tahunan',
        'reason' => 'Keperluan keluarga',
        'status' => 'pending',
    ]);

    // 2. Initiate Workflow
    $workflowService = app(WorkflowService::class);
    $workflowRequest = $workflowService->initiate('leave_annual_approval', $this->guru, $leave, 'Mohon disetujui');

    $this->assertDatabaseHas('workflow_requests', [
        'id' => $workflowRequest->id,
        'status' => 'pending',
        'current_step' => 1,
    ]);

    // 3. First step approval (waka_kurikulum)
    $this->actingAs($this->wakaKur)->post(route('workflows.approve', $workflowRequest), [
        'remarks' => 'Rekomendasi disetujui',
    ])->assertRedirect(route('workflows.pending'));

    $workflowRequest->refresh();
    expect($workflowRequest->current_step)->toBe(2);
    expect($workflowRequest->status)->toBe('in_review');

    // 4. Second step requests revision (kepala_sekolah)
    $this->actingAs($this->kepsek)->post(route('workflows.revise', $workflowRequest), [
        'remarks' => 'Mohon ubah tanggal mulai cuti agar tidak bentrok ujian.',
    ])->assertRedirect(route('workflows.pending'));

    $workflowRequest->refresh();
    expect($workflowRequest->status)->toBe('revision');
    expect(in_array($leave->fresh()->status, ['revision', 'pending']))->toBeTrue();

    // 5. Requester resubmits
    $this->actingAs($this->guru)->post(route('workflows.resubmit', $workflowRequest), [
        'notes' => 'Sudah saya sesuaikan tanggalnya di dokumen.',
    ])->assertRedirect(route('workflows.my-requests'));

    $workflowRequest->refresh();
    expect($workflowRequest->status)->toBe('pending');
    expect($workflowRequest->current_step)->toBe(1);
    expect($workflowRequest->approvals()->where('decision', '!=', 'pending')->count())->toBe(0);
    expect($leave->fresh()->status)->toBe('pending');
});

// ── 4. DELEGATION FLOW ────────────────────────────────────────────────

test('approver can delegate step to specific user', function () {
    // Create delegated user with BK role for testing delegation capability
    $delegatee = User::factory()->create(['role' => 'bk']);

    $leave = LeaveRequest::create([
        'employee_id' => $this->employee->id,
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(5),
        'type' => 'cuti_tahunan',
        'reason' => 'Keperluan keluarga',
        'status' => 'pending',
    ]);

    $workflowService = app(WorkflowService::class);
    $workflowRequest = $workflowService->initiate('leave_annual_approval', $this->guru, $leave, 'Ujian cuti');

    // Delegate step 1 (waka_kurikulum) to $delegatee
    $this->actingAs($this->wakaKur)->post(route('workflows.delegate', $workflowRequest), [
        'delegatee_id' => $delegatee->id,
        'remarks' => 'Tolong di-review selagi saya dinas luar.',
    ])->assertRedirect(route('workflows.pending'));

    $step1 = $workflowRequest->approvals()->where('step_order', 1)->first();
    expect($step1->delegated_to_id)->toBe($delegatee->id);
    expect($step1->remarks)->toContain('Didelegasikan');

    // Verify delegatee has access to approve it
    $this->actingAs($delegatee)->post(route('workflows.approve', $workflowRequest), [
        'remarks' => 'Disetujui atas nama delegasi',
    ])->assertRedirect(route('workflows.pending'));

    $workflowRequest->refresh();
    expect($workflowRequest->current_step)->toBe(2);
    expect($step1->fresh()->decision)->toBe('approved');
    expect($step1->fresh()->approver_id)->toBe($delegatee->id);
});
