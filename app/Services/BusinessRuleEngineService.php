<?php

namespace App\Services;

use App\Models\BpaBusinessRule;
use App\Models\BpaRuleExecution;
use Illuminate\Support\Facades\Log;

class BusinessRuleEngineService
{
    /**
     * Create a new business automation rule.
     */
    public function createRule(array $data): BpaBusinessRule
    {
        return BpaBusinessRule::create([
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'conditions' => (array) $data['conditions'],
            'actions' => (array) $data['actions'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Evaluate context against rules and execute actions if conditions match.
     */
    public function evaluateAndExecute(string $triggerEvent, array $context): array
    {
        $rules = BpaBusinessRule::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->get();

        $executionSummaries = [];

        foreach ($rules as $rule) {
            $startTime = microtime(true);
            $isMatched = $this->evaluateConditions($rule->conditions, $context);
            $executedActions = [];
            $status = 'SKIPPED';

            if ($isMatched) {
                foreach ($rule->actions as $action) {
                    $actionResult = $this->executeAction($action, $context);
                    $executedActions[] = [
                        'action_type' => $action['action_type'] ?? 'unknown',
                        'result' => $actionResult,
                    ];
                }
                $status = 'SUCCESS';
            }

            $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

            $executionLog = BpaRuleExecution::create([
                'bpa_business_rule_id' => $rule->id,
                'trigger_event' => $triggerEvent,
                'matched' => $isMatched,
                'actions_executed' => $executedActions,
                'status' => $status,
                'execution_time_ms' => $latencyMs,
            ]);

            $executionSummaries[] = [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'matched' => $isMatched,
                'executed_actions_count' => count($executedActions),
                'execution_time_ms' => $latencyMs,
            ];
        }

        return [
            'trigger_event' => $triggerEvent,
            'rules_evaluated' => count($rules),
            'executions' => $executionSummaries,
        ];
    }

    /**
     * Helper to evaluate a set of conditions against execution context.
     */
    public function evaluateConditions(array $conditions, array $context): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $cond) {
            $field = $cond['field'] ?? null;
            $operator = $cond['operator'] ?? '==';
            $expectedValue = $cond['value'] ?? null;

            if (! $field || ! array_key_exists($field, $context)) {
                return false;
            }

            $actualValue = $context[$field];

            $match = match ($operator) {
                '==' => $actualValue == $expectedValue,
                '!=' => $actualValue != $expectedValue,
                '<' => $actualValue < $expectedValue,
                '>' => $actualValue > $expectedValue,
                '<=' => $actualValue <= $expectedValue,
                '>=' => $actualValue >= $expectedValue,
                'contains' => is_string($actualValue) && str_contains(strtolower($actualValue), strtolower((string) $expectedValue)),
                default => false,
            };

            if (! $match) {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute a specific automated business action.
     */
    private function executeAction(array $action, array $context): array
    {
        $actionType = $action['action_type'] ?? '';
        $params = $action['params'] ?? [];

        Log::info("BPA Action Executing [{$actionType}]:", array_merge($params, $context));

        return match ($actionType) {
            'issue_counseling_ticket' => [
                'ticket_no' => 'BK-'.rand(1000, 9999),
                'status' => 'ISSUED',
                'whatsapp_notification' => 'SENT_TO_PARENT',
            ],
            'apply_payroll_deduction' => [
                'deduction_amount' => $params['deduction_amount'] ?? 50000,
                'payroll_status' => 'DEDUCTION_APPLIED',
            ],
            'issue_warning_letter_sp1' => [
                'sp_number' => 'SP1/SMA-SA1/'.date('Y/m'),
                'status' => 'GENERATED',
                'homeroom_notification' => 'NOTIFIED',
            ],
            'send_whatsapp_alert' => [
                'recipient' => $params['recipient'] ?? 'parent',
                'status' => 'QUEUED_FONNTE',
            ],
            default => [
                'status' => 'EXECUTED',
            ],
        };
    }
}
