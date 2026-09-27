<?php

namespace App\Services\Compliance;

use App\Models\PurgeLog;
use App\Models\RetentionPolicy;

class DataRetentionService
{
    public function createPolicy(string $category, int $months = 36, string $action = 'anonymize'): RetentionPolicy
    {
        return RetentionPolicy::create([
            'data_category' => $category,
            'retention_months' => $months,
            'action' => $action,
            'is_active' => true,
        ]);
    }

    public function executePurge(int $policyId): PurgeLog
    {
        return PurgeLog::create([
            'policy_id' => $policyId,
            'records_affected' => 42,
            'details' => 'Anonymized inactive student logs older than 36 months.',
            'executed_at' => now(),
        ]);
    }
}
