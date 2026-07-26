<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Deployment\E2eTestService;
use App\Services\Deployment\EnvironmentConfigService;
use App\Services\Deployment\ReleaseManagerService;
use Illuminate\Database\Seeder;

class DeploymentReadinessSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);

        // 1. Environment Secrets Configuration
        $envService = app(EnvironmentConfigService::class);
        $envService->setConfig('APP_KEY', 'base64:9aK7xR3L8vQ2wM5nP0zY4bC6dF1gH3jK5lM7nP9qR1s=', 'production', 'security', true, $superadmin->name);
        $envService->setConfig('DB_PASSWORD', 'p@ssw0rd_sista_prod_2026', 'production', 'database', true, $superadmin->name);
        $envService->setConfig('WHATSAPP_API_TOKEN', 'wa_live_sec_884920193481203', 'production', 'api_keys', true, $superadmin->name);

        // 2. SemVer Release Version
        $relService = app(ReleaseManagerService::class);
        $relService->createRelease('1.0.0', 'Rilis Perdana Enterprise SISTA SULTAN AGUNG 1 Semarang Full Suite (Fase 1 - 38)', 'major', $superadmin->name);

        // 3. E2E Test Runs
        $e2eService = app(E2eTestService::class);
        $e2eService->runSuite('Full Enterprise E2E Suite (10 Core Scenarios)', 'Chromium Headless');
    }
}
