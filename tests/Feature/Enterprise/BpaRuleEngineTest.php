<?php

use App\Models\User;
use App\Services\BusinessRuleEngineService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->ruleEngine = app(BusinessRuleEngineService::class);
});

test('business rule engine evaluates conditions and reports matched actions as not executed', function () {
    $rule = $this->ruleEngine->createRule([
        'name' => 'Low Attendance BK Ticket Rule',
        'trigger_event' => 'attendance.checked',
        'conditions' => [
            ['field' => 'attendance_percentage', 'operator' => '<', 'value' => 80],
        ],
        'actions' => [
            ['action_type' => 'issue_counseling_ticket', 'params' => []],
        ],
        'is_active' => true,
    ]);

    expect($rule->name)->toBe('Low Attendance BK Ticket Rule');

    $result = $this->ruleEngine->evaluateAndExecute('attendance.checked', [
        'attendance_percentage' => 70.0,
    ]);

    expect($result['rules_evaluated'])->toBe(1);
    expect($result['executions'][0]['matched'])->toBeTrue();
    // No action has an executor yet, so none is reported as carried out
    // (this used to claim 1, with a random ticket number).
    expect($result['executions'][0]['executed_actions_count'])->toBe(0);
    expect($result['executions'][0]['not_executed_actions'])->toBe(['issue_counseling_ticket']);
    expect($result['executions'][0]['status'])->toBe('NOT_EXECUTED');
});

test('business rule engine skips execution when conditions fail to match', function () {
    $this->ruleEngine->createRule([
        'name' => 'Lateness Penalty Rule',
        'trigger_event' => 'employee.lateness',
        'conditions' => [
            ['field' => 'lateness_count', 'operator' => '>', 'value' => 3],
        ],
        'actions' => [
            ['action_type' => 'apply_payroll_deduction', 'params' => []],
        ],
        'is_active' => true,
    ]);

    $result = $this->ruleEngine->evaluateAndExecute('employee.lateness', [
        'lateness_count' => 1,
    ]);

    expect($result['executions'][0]['matched'])->toBeFalse();
    expect($result['executions'][0]['executed_actions_count'])->toBe(0);
});

test('admin can view bpa rule engine console', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.bpa.rules.index'));
    $response->assertStatus(200);
    $response->assertSee('No-Code Business Process Automation');
    $response->assertSee('Rule Engine');
});

test('admin can store new business automation rule via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.bpa.rules.index'))
        ->post(route('admin.bpa.rules.store'), [
            'name' => 'Controller Test Rule',
            'trigger_event' => 'student.point_logged',
            'conditions' => [
                ['field' => 'penalty_points', 'operator' => '>', 'value' => 50],
            ],
            'actions' => [
                ['action_type' => 'issue_warning_letter_sp1', 'params' => []],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.bpa.rules.index'));
});

test('admin can simulate test execution via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.bpa.rules.index'))
        ->post(route('admin.bpa.rules.test-execute'), [
            'trigger_event' => 'attendance.checked',
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.bpa.rules.index'));
});
