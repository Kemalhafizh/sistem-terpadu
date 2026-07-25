<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use App\Models\User;
use App\Services\Integration\EtlPipelineService;
use App\Services\Integration\PluginManagerService;
use Illuminate\Database\Seeder;

class EnterpriseIntegrationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        // 31.1 Seed API Client
        ApiClient::create([
            'name' => 'Aplikasi Mobile Wali Murid Sultan Agung 1',
            'client_id' => 'client_mobile_app',
            'client_secret' => 'secret_mobile_app_sec_2026',
            'allowed_scopes' => ['read', 'write', 'graphql'],
            'rate_limit_per_minute' => 120,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        // 31.2 Seed ETL Job
        $etlService = app(EtlPipelineService::class);
        $etlService->createJob([
            'name' => 'Migrasi Data Siswa Dapodik TA 2025/2026',
            'source_type' => 'dapodik_xml',
            'source_file_path' => 'imports/dapodik_siswa_2026.xml',
            'target_table' => 'students',
            'records_total' => 450,
        ], $user->id);

        // 31.3 Seed Plugin Extension
        $pluginService = app(PluginManagerService::class);
        $plugin = $pluginService->install([
            'name' => 'Google Classroom Sync Extension',
            'slug' => 'google-classroom-sync',
            'version' => '1.0.0',
            'author' => 'SISTA Ecosystem Team',
        ]);
        $pluginService->registerHook($plugin->id, 'dashboard.widget', 'App\\Plugins\\GoogleClassroom', 'renderWidget');
    }
}
