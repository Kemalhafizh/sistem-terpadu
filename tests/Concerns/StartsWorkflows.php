<?php

namespace Tests\Concerns;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Services\WorkflowService;

/**
 * Starts a real approval workflow (definition, approval steps, and a leave
 * request as the record being approved) through WorkflowService, the way the
 * app does, instead of inserting a bare WorkflowRequest without its steps.
 */
trait StartsWorkflows
{
    /** @param list<string> $roles one approval step per role, in order */
    protected function startLeaveWorkflow(User $requester, array $roles, string $name = 'Pengajuan Cuti', ?string $notes = null): WorkflowRequest
    {
        $code = 'WF-'.uniqid();
        WorkflowDefinition::create([
            'name' => $name,
            'code' => $code,
            'is_active' => true,
            'steps' => array_map(fn (string $role, int $i) => ['order' => $i + 1, 'role' => $role], $roles, array_keys($roles)),
        ]);

        $employee = Employee::firstOrCreate(['user_id' => $requester->id], ['position' => 'Guru']);
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'type' => 'izin',
            'reason' => 'Keperluan keluarga',
        ]);

        return app(WorkflowService::class)->initiate($code, $requester, $leave, $notes);
    }
}
