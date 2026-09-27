<?php

namespace Database\Seeders;

use App\Services\ImmutableAuditLedgerService;
use Illuminate\Database\Seeder;

class ImmutableAuditLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $ledger = new ImmutableAuditLedgerService;

        // Seed 3 linked cryptographic audit entries
        $ledger->record(
            'GRADE_MUTATED',
            'App\Models\Grade',
            101,
            ['score' => 70],
            ['score' => 88]
        );

        $ledger->record(
            'FINANCIAL_TRANSACTION',
            'App\Models\FinanceTransaction',
            202,
            ['status' => 'PENDING'],
            ['status' => 'PAID', 'amount' => 500000]
        );

        $ledger->record(
            'ROLE_CHANGED',
            'App\Models\User',
            1,
            ['role' => 'guru'],
            ['role' => 'admin']
        );
    }
}
