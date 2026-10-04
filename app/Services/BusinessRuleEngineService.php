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
            $status = 'SKIPPED';
            $pendingActions = [];

            if ($isMatched) {
                foreach ($rule->actions as $action) {
                    $pendingActions[] = [
                        'action_type' => $action['action_type'] ?? 'unknown',
                        'result' => $this->executeAction($action, $context),
                    ];
                }
                $status = $pendingActions === [] ? 'SUCCESS' : 'NOT_EXECUTED';
            }

            $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

            BpaRuleExecution::create([
                'bpa_business_rule_id' => $rule->id,
                'trigger_event' => $triggerEvent,
                'matched' => $isMatched,
                'actions_executed' => $pendingActions,
                'status' => $status,
                'execution_time_ms' => $latencyMs,
            ]);

            $executionSummaries[] = [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'matched' => $isMatched,
                // Nothing is carried out yet (see executeAction), so this stays 0.
                'executed_actions_count' => 0,
                'not_executed_actions' => array_column($pendingActions, 'action_type'),
                'status' => $status,
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
     * Carry out an action of a matched rule.
     *
     * No action has an executor yet: nothing creates a counseling ticket,
     * sends WhatsApp, deducts payroll or issues a warning letter. This used to
     * answer as if each had happened (a random "BK-1234" ticket number,
     * "SENT_TO_PARENT", "DEDUCTION_APPLIED"). Now it says what it did: nothing.
     */
    private function executeAction(array $action, array $context): array
    {
        $actionType = $action['action_type'] ?? 'unknown';

        Log::info("BPA action [{$actionType}] matched but has no executor yet; nothing was done.", $context);

        return [
            'status' => 'NOT_IMPLEMENTED',
            'message' => "Aksi [{$actionType}] belum memiliki pelaksana. Tidak ada tiket, pesan, potongan, atau surat yang dibuat.",
        ];
    }
}
