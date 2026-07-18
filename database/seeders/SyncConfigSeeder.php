<?php

namespace Database\Seeders;

use App\Models\SyncConfig;
use App\Models\SyncReport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\Integration\SyncMapperService;
use Carbon\Carbon;

class SyncConfigSeeder extends Seeder
{
    public function run(): void
    {
        $syncService = app(SyncMapperService::class);
        $admin = User::where('role', 'admin')->first() ?: User::first();

        // 1. Seed configurations
        $configs = [
            [
                'system_target' => 'dapodik',
                'local_table' => 'students',
                'field_mapping' => $syncService->getDefaultMapping('dapodik', 'students'),
            ],
            [
                'system_target' => 'dapodik',
                'local_table' => 'teachers',
                'field_mapping' => $syncService->getDefaultMapping('dapodik', 'teachers'),
            ],
            [
                'system_target' => 'emis',
                'local_table' => 'students',
                'field_mapping' => $syncService->getDefaultMapping('emis', 'students'),
            ],
            [
                'system_target' => 'emis',
                'local_table' => 'teachers',
                'field_mapping' => $syncService->getDefaultMapping('emis', 'teachers'),
            ],
        ];

        foreach ($configs as $config) {
            SyncConfig::updateOrCreate(
                [
                    'system_target' => $config['system_target'],
                    'local_table' => $config['local_table']
                ],
                [
                    'field_mapping' => $config['field_mapping']
                ]
            );
        }

        // 2. Seed Sync Reports
        $systems = ['dapodik', 'emis'];
        $tables = ['students', 'teachers'];

        for ($i = 0; $i < 10; $i++) {
            $target = collect($systems)->random();
            $table = collect($tables)->random();

            $date = Carbon::now()->subDays(rand(0, 7))->setTime(rand(8, 16), rand(0, 59));
            $processed = rand(30, 80);
            $failed = rand(0, 3);
            $success = $processed - $failed;

            $errorLogs = [];
            if ($failed > 0) {
                if ($table === 'students') {
                    $errorLogs = [
                        [
                            'id' => 1,
                            'name' => 'Ahmad Fauzi',
                            'identifier' => 'NIS: 2026101',
                            'field' => 'nisn',
                            'issue' => 'NISN tidak valid (harus 10 digit).',
                            'severity' => 'warning',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Siti Aminah',
                            'identifier' => 'NIS: 2026102',
                            'field' => 'gender',
                            'issue' => 'Jenis kelamin kosong.',
                            'severity' => 'error',
                        ]
                    ];
                } else {
                    $errorLogs = [
                        [
                            'id' => 3,
                            'name' => 'Bp. Bambang, M.Pd',
                            'identifier' => 'NIP: -',
                            'field' => 'nip',
                            'issue' => 'Status PNS tetapi NIP kosong.',
                            'severity' => 'error',
                        ]
                    ];
                }
            }

            SyncReport::create([
                'system_target' => $target,
                'local_table' => $table,
                'sync_run_at' => $date,
                'records_processed' => $processed,
                'records_success' => $success,
                'records_failed' => $failed,
                'error_logs' => $errorLogs,
                'run_by' => $admin->id,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
