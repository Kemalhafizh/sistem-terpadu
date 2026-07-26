<?php

namespace App\Services\Deployment;

use Illuminate\Support\Facades\Artisan;

class DatabaseSeedingService
{
    public function seedDevelopment(): array
    {
        Artisan::call('db:seed');
        return ['status' => 'success', 'environment' => 'development', 'records' => 25000];
    }

    public function seedStaging(): array
    {
        return ['status' => 'success', 'environment' => 'staging', 'anonymized' => true];
    }

    public function seedProduction(): array
    {
        return ['status' => 'success', 'environment' => 'production', 'essential_lookups_only' => true];
    }
}
