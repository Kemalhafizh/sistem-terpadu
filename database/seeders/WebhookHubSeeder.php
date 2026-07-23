<?php

namespace Database\Seeders;

use App\Services\WebhookManagerService;
use Illuminate\Database\Seeder;

class WebhookHubSeeder extends Seeder
{
    public function run(): void
    {
        /** @var WebhookManagerService $webhookManager */
        $webhookManager = app(WebhookManagerService::class);

        $sub = $webhookManager->registerSubscription([
            'name' => 'Portal Yayasan YBWSA Pusat',
            'target_url' => 'https://api.ybwsa.or.id/webhooks/sista',
            'secret_key' => 'whsec_demo_ybwsa_secret_key_12345',
            'events' => ['student.registered', 'billing.paid'],
            'is_active' => true,
        ]);

        $webhookManager->dispatch('student.registered', [
            'student_id' => 1,
            'nisn' => '0012345678',
            'name' => 'Siswa Seeder Demo',
            'grade' => 'X-A',
        ]);
    }
}
