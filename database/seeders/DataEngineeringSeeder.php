<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\EventStream;
use App\Models\NlqSchemaMap;
use App\Models\NlqQueryLog;
use App\Models\ConditionalReport;
use App\Models\ReportScheduleLog;
use App\Models\DataQualityRule;
use App\Models\AnomalyRecord;
use App\Models\User;
use Illuminate\Support\Str;

class DataEngineeringSeeder extends Seeder {
    public function run(): void {
        $admin = User::first() ?? User::factory()->create();

        EventStream::create([
            'id' => Str::uuid()->toString(),
            'aggregate_type' => 'student',
            'aggregate_id' => 'STU-001',
            'event_type' => 'student_registered',
            'payload' => ['name' => 'Budi', 'grade' => 'X'],
            'created_at' => now(),
        ]);

        NlqSchemaMap::create([
            'table_name' => 'students',
            'column_name' => 'gpa',
            'indonesian_alias' => 'nilai rata-rata',
        ]);

        NlqQueryLog::create([
            'user_id' => $admin->id,
            'natural_query' => 'tampilkan siswa dengan nilai rata-rata tertinggi',
            'generated_sql' => 'SELECT * FROM students ORDER BY gpa DESC LIMIT 10',
        ]);

        $report = ConditionalReport::create([
            'report_name' => 'Laporan Nilai Anjlok',
            'view_template' => 'reports.low_grades',
            'cron_schedule' => '0 0 * * 0',
            'distribution_list' => ['kepsek@example.com'],
            'trigger_condition_sql' => 'SELECT COUNT(*) FROM students WHERE gpa < 2.0',
            'created_by' => $admin->id,
        ]);

        ReportScheduleLog::create([
            'conditional_report_id' => $report->id,
            'status' => 'skipped',
        ]);

        $rule = DataQualityRule::create([
            'target_table' => 'students',
            'target_column' => 'nisn',
            'rule_type' => 'format',
            'rule_definition' => '/^[0-9]{10}$/',
        ]);

        AnomalyRecord::create([
            'data_quality_rule_id' => $rule->id,
            'record_id' => '100',
            'invalid_data' => ['nisn' => 'ABC12345'],
            'status' => 'detected',
        ]);
    }
}
