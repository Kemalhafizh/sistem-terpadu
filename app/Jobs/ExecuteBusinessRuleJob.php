<?php

namespace App\Jobs;

use App\Services\BusinessRuleEngineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExecuteBusinessRuleJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $triggerEvent,
        public array $context
    ) {
        $this->onQueue('default');
    }

    public function handle(BusinessRuleEngineService $ruleEngine): void
    {
        Log::info("BPA Worker: Evaluasi aturan bisnis untuk event [{$this->triggerEvent}]...");

        $res = $ruleEngine->evaluateAndExecute($this->triggerEvent, $this->context);

        Log::info("BPA Worker: Evaluasi selesai. Aturan dievaluasi: {$res['rules_evaluated']}.");
    }
}
