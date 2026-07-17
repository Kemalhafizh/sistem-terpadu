<?php

use App\Models\User;
use App\Models\WorkflowDefinition;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);
});

test('admin can access workflow definitions page', function () {
    $response = $this->actingAs($this->admin)->get(route('workflows.definitions'));
    $response->assertStatus(200);
});

test('admin can store new workflow definition', function () {
    $response = $this->actingAs($this->admin)->post(route('workflows.definitions.store'), [
        'name' => 'Pengajuan Anggaran OSIS',
        'code' => 'OSIS_BUDGET',
        'description' => 'Persetujuan pengeluaran anggaran untuk kegiatan OSIS.',
        'is_active' => 'on',
        'steps' => [
            [
                'order' => 1,
                'role' => 'waka_kesiswaan',
                'sla_hours' => 24,
            ],
            [
                'order' => 2,
                'role' => 'kepala_sekolah',
                'sla_hours' => 48,
            ]
        ]
    ]);

    $response->assertRedirect(route('workflows.definitions'));
    $this->assertDatabaseHas('workflow_definitions', [
        'code' => 'OSIS_BUDGET',
        'is_active' => true,
    ]);
});

test('admin can delete workflow definition', function () {
    $definition = WorkflowDefinition::create([
        'name' => 'Pengajuan Cuti Guru',
        'code' => 'TEACHER_LEAVE',
        'description' => 'Workflow untuk cuti.',
        'steps' => [
            ['order' => 1, 'role' => 'waka_kurikulum', 'sla_hours' => 24]
        ],
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)->delete(route('workflows.definitions.delete', $definition->id));

    $response->assertRedirect(route('workflows.definitions'));
    $this->assertDatabaseMissing('workflow_definitions', [
        'id' => $definition->id,
    ]);
});
