<?php

namespace Database\Seeders;

use App\Services\DisasterRecoveryService;
use Illuminate\Database\Seeder;

class DisasterRecoverySeeder extends Seeder
{
    public function run(): void
    {
        /** @var DisasterRecoveryService $drService */
        $drService = app(DisasterRecoveryService::class);
        $drService->createSnapshot('initial_seed');
    }
}
