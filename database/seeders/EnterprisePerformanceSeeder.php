<?php

namespace Database\Seeders;

use App\Models\QueryPerformanceLog;
use App\Models\SystemScalingConfig;
use App\Services\Performance\LoadTestService;
use Illuminate\Database\Seeder;

class EnterprisePerformanceSeeder extends Seeder
{
    public function run(): void
    {
        // 32.1 Seed Query Performance Logs
        QueryPerformanceLog::create([
            'route' => 'students.index',
            'query_sql' => 'select * from students where status = ?',
            'execution_time_ms' => 18.5,
            'is_slow' => false,
        ]);

        QueryPerformanceLog::create([
            'route' => 'academic.reports.export',
            'query_sql' => 'select * from student_grades join students on student_grades.student_id = students.id where academic_year = ?',
            'execution_time_ms' => 245.8,
            'is_slow' => true,
        ]);

        // 32.3 Seed Load Test Result
        $loadService = app(LoadTestService::class);
        $loadService->runScenario('Ujian CBT Online Massal', 250, 30);

        // 32.4 Seed System Scaling Config
        SystemScalingConfig::create([
            'config_key' => 'db_read_replica_hosts',
            'config_value' => 'db-replica-1.sultanagung1.sch.id, db-replica-2.sultanagung1.sch.id',
            'description' => 'Node cluster read-only database MySQL.',
            'is_active' => true,
        ]);

        SystemScalingConfig::create([
            'config_key' => 'redis_session_cluster_host',
            'config_value' => 'redis-cluster.sultanagung1.sch.id:6379',
            'description' => 'Externalized Redis Session Storage.',
            'is_active' => true,
        ]);
    }
}
