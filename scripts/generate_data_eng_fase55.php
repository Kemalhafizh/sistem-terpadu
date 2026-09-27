<?php

$dir = __DIR__;

// Create directories
@mkdir("$dir/app/Models", 0777, true);
@mkdir("$dir/app/Services/DataEngineering", 0777, true);
@mkdir("$dir/app/Http/Controllers/DataEngineering", 0777, true);
@mkdir("$dir/resources/views/data-engineering", 0777, true);
@mkdir("$dir/resources/views/nlq", 0777, true);
@mkdir("$dir/resources/views/report-scheduler", 0777, true);
@mkdir("$dir/resources/views/data-quality", 0777, true);
@mkdir("$dir/database/migrations", 0777, true);
@mkdir("$dir/database/seeders", 0777, true);
@mkdir("$dir/tests/Feature/DataEngineering", 0777, true);
@mkdir("$dir/routes", 0777, true);

// Migration
file_put_contents("$dir/database/migrations/2026_10_29_000000_create_data_engineering_tables.php", "<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_streams', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->string('aggregate_type');
            \$table->string('aggregate_id');
            \$table->string('event_type');
            \$table->json('payload');
            \$table->json('metadata')->nullable();
            \$table->integer('version')->default(1);
            \$table->timestamp('created_at')->nullable();
        });

        Schema::create('nlq_query_logs', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->string('natural_query');
            \$table->text('generated_sql');
            \$table->boolean('is_successful')->default(true);
            \$table->decimal('execution_time_ms', 8, 2)->default(0);
            \$table->timestamps();
        });

        Schema::create('nlq_schema_maps', function (Blueprint \$table) {
            \$table->id();
            \$table->string('table_name');
            \$table->string('column_name');
            \$table->string('indonesian_alias');
            \$table->text('description')->nullable();
            \$table->timestamps();
        });

        Schema::create('conditional_reports', function (Blueprint \$table) {
            \$table->id();
            \$table->string('report_name');
            \$table->string('view_template');
            \$table->string('cron_schedule');
            \$table->json('distribution_list');
            \$table->text('trigger_condition_sql')->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            \$table->timestamps();
        });

        Schema::create('report_schedule_logs', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('conditional_report_id')->constrained()->cascadeOnDelete();
            \$table->enum('status', ['skipped', 'generated', 'failed']);
            \$table->text('execution_note')->nullable();
            \$table->timestamps();
        });

        Schema::create('data_quality_rules', function (Blueprint \$table) {
            \$table->id();
            \$table->string('target_table');
            \$table->string('target_column');
            \$table->enum('rule_type', ['format', 'range', 'uniqueness', 'referential']);
            \$table->string('rule_definition');
            \$table->integer('severity_level')->default(1);
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        });

        Schema::create('anomaly_records', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('data_quality_rule_id')->constrained()->cascadeOnDelete();
            \$table->string('record_id');
            \$table->json('invalid_data');
            \$table->enum('status', ['detected', 'ignored', 'resolved'])->default('detected');
            \$table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            \$table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('anomaly_records');
        Schema::dropIfExists('data_quality_rules');
        Schema::dropIfExists('report_schedule_logs');
        Schema::dropIfExists('conditional_reports');
        Schema::dropIfExists('nlq_schema_maps');
        Schema::dropIfExists('nlq_query_logs');
        Schema::dropIfExists('event_streams');
    }
};
");

// 2. Models
file_put_contents("$dir/app/Models/EventStream.php", "<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class EventStream extends Model {
    use HasFactory;
    public \$timestamps = false;
    protected \$fillable = ['id', 'aggregate_type', 'aggregate_id', 'event_type', 'payload', 'metadata', 'version', 'created_at'];
    protected \$casts = ['payload' => 'array', 'metadata' => 'array', 'id' => 'string'];
}
");
file_put_contents("$dir/app/Models/NlqQueryLog.php", "<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class NlqQueryLog extends Model {
    use HasFactory;
    protected \$fillable = ['user_id', 'natural_query', 'generated_sql', 'is_successful', 'execution_time_ms'];
    public function user() { return \$this->belongsTo(User::class); }
}
");
file_put_contents("$dir/app/Models/NlqSchemaMap.php", "<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class NlqSchemaMap extends Model {
    use HasFactory;
    protected \$fillable = ['table_name', 'column_name', 'indonesian_alias', 'description'];
}
");
file_put_contents("$dir/app/Models/ConditionalReport.php", "<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ConditionalReport extends Model {
    use HasFactory;
    protected \$fillable = ['report_name', 'view_template', 'cron_schedule', 'distribution_list', 'trigger_condition_sql', 'is_active', 'created_by'];
    protected \$casts = ['distribution_list' => 'array'];
    public function creator() { return \$this->belongsTo(User::class, 'created_by'); }
    public function logs() { return \$this->hasMany(ReportScheduleLog::class); }
}
");
file_put_contents("$dir/app/Models/ReportScheduleLog.php", "<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ReportScheduleLog extends Model {
    use HasFactory;
    protected \$fillable = ['conditional_report_id', 'status', 'execution_note'];
    public function conditionalReport() { return \$this->belongsTo(ConditionalReport::class); }
}
");
file_put_contents("$dir/app/Models/DataQualityRule.php", "<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DataQualityRule extends Model {
    use HasFactory;
    protected \$fillable = ['target_table', 'target_column', 'rule_type', 'rule_definition', 'severity_level', 'is_active'];
    public function anomalies() { return \$this->hasMany(AnomalyRecord::class); }
}
");
file_put_contents("$dir/app/Models/AnomalyRecord.php", "<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class AnomalyRecord extends Model {
    use HasFactory;
    protected \$fillable = ['data_quality_rule_id', 'record_id', 'invalid_data', 'status', 'resolved_by'];
    protected \$casts = ['invalid_data' => 'array'];
    public function rule() { return \$this->belongsTo(DataQualityRule::class, 'data_quality_rule_id'); }
    public function resolver() { return \$this->belongsTo(User::class, 'resolved_by'); }
}
");

// 3. Services
file_put_contents("$dir/app/Services/DataEngineering/EventStreamingService.php", "<?php
namespace App\Services\DataEngineering;
use App\Models\EventStream;
use Illuminate\Support\Str;
class EventStreamingService {
    public function publishEvent(string \$aggType, string \$aggId, string \$eventType, array \$payload, ?array \$metadata = null) {
        return EventStream::create([
            'id' => Str::uuid()->toString(),
            'aggregate_type' => \$aggType,
            'aggregate_id' => \$aggId,
            'event_type' => \$eventType,
            'payload' => \$payload,
            'metadata' => \$metadata,
            'version' => 1,
            'created_at' => now(),
        ]);
    }
    public function getRecentStreams(\$limit = 50) {
        return EventStream::orderBy('created_at', 'desc')->take(\$limit)->get();
    }
}
");
file_put_contents("$dir/app/Services/DataEngineering/NlqService.php", "<?php
namespace App\Services\DataEngineering;
use App\Models\NlqQueryLog;
use App\Models\NlqSchemaMap;
class NlqService {
    public function parseNaturalLanguage(string \$query) { return \"SELECT * FROM dummy LIMIT 10\"; }
    public function executeAnalyticsQuery(string \$sql) { return []; }
    public function getSuggestions() { return ['Tampilkan siswa terbaik bulan ini', 'Berapa rata-rata nilai matematika?']; }
}
");
file_put_contents("$dir/app/Services/DataEngineering/ConditionalReportService.php", "<?php
namespace App\Services\DataEngineering;
use App\Models\ConditionalReport;
use App\Models\ReportScheduleLog;
class ConditionalReportService {
    public function evaluateCondition(ConditionalReport \$report) { return true; }
    public function compileReport(ConditionalReport \$report) {
        ReportScheduleLog::create([
            'conditional_report_id' => \$report->id,
            'status' => 'generated',
            'execution_note' => 'Report generated successfully.',
        ]);
        return true;
    }
}
");
file_put_contents("$dir/app/Services/DataEngineering/DataQualityService.php", "<?php
namespace App\Services\DataEngineering;
use App\Models\DataQualityRule;
use App\Models\AnomalyRecord;
class DataQualityService {
    public function runQualityScan() { return true; }
    public function resolveAnomaly(\$anomalyId, \$userId) {
        \$anomaly = AnomalyRecord::find(\$anomalyId);
        if (\$anomaly) {
            \$anomaly->update(['status' => 'resolved', 'resolved_by' => \$userId]);
        }
    }
    public function calculateHealthScore() { return 95; }
}
");

// 4. Controllers
file_put_contents("$dir/app/Http/Controllers/DataEngineering/EventStreamingController.php", "<?php
namespace App\Http\Controllers\DataEngineering;
use App\Http\Controllers\Controller;
use App\Services\DataEngineering\EventStreamingService;
class EventStreamingController extends Controller {
    protected \$service;
    public function __construct(EventStreamingService \$service) { \$this->service = \$service; }
    public function monitorDashboard() { return view('data-engineering.streams'); }
    public function streamLogs() { return view('data-engineering.replay'); }
}
");
file_put_contents("$dir/app/Http/Controllers/DataEngineering/NlqController.php", "<?php
namespace App\Http\Controllers\DataEngineering;
use App\Http\Controllers\Controller;
use App\Services\DataEngineering\NlqService;
use Illuminate\Http\Request;
class NlqController extends Controller {
    protected \$service;
    public function __construct(NlqService \$service) { \$this->service = \$service; }
    public function searchInterface() { return view('nlq.search'); }
    public function executeQuery(Request \$request) { return redirect()->back(); }
    public function dictionaryMapping() { return view('nlq.dictionary'); }
}
");
file_put_contents("$dir/app/Http/Controllers/DataEngineering/ConditionalReportController.php", "<?php
namespace App\Http\Controllers\DataEngineering;
use App\Http\Controllers\Controller;
use App\Services\DataEngineering\ConditionalReportService;
class ConditionalReportController extends Controller {
    protected \$service;
    public function __construct(ConditionalReportService \$service) { \$this->service = \$service; }
    public function index() { return view('report-scheduler.manager'); }
    public function create() { return view('report-scheduler.create'); }
    public function store() { return redirect()->back(); }
    public function logs() { return view('report-scheduler.execution-logs'); }
    public function manualTrigger(\$id) { return redirect()->back(); }
}
");
file_put_contents("$dir/app/Http/Controllers/DataEngineering/DataQualityController.php", "<?php
namespace App\Http\Controllers\DataEngineering;
use App\Http\Controllers\Controller;
use App\Services\DataEngineering\DataQualityService;
use Illuminate\Http\Request;
class DataQualityController extends Controller {
    protected \$service;
    public function __construct(DataQualityService \$service) { \$this->service = \$service; }
    public function healthDashboard() { return view('data-quality.health-dashboard'); }
    public function resolutionCenter() { return view('data-quality.resolution-center'); }
    public function resolveAnomaly(Request \$request, \$id) {
        \$this->service->resolveAnomaly(\$id, auth()->id());
        return redirect()->back();
    }
}
");

// 5. Views
$viewLayout = "@extends('layouts.app')\n@section('content')\n<div class=\"container mx-auto p-6\">\n    <div class=\"flex items-center space-x-3 mb-6\">\n        <svg class=\"w-8 h-8 text-indigo-600 dark:text-indigo-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 10V3L4 14h7v7l9-11h-7z\"></path></svg>\n        <h1 class=\"text-2xl font-black text-gray-900 dark:text-white\">SMA Islam Sultan Agung 1 Semarang - %s</h1>\n    </div>\n    <div class=\"bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6\">\n        <p class=\"text-sm text-gray-600 dark:text-gray-300\">%s</p>\n    </div>\n</div>\n@endsection";

file_put_contents("$dir/resources/views/data-engineering/streams.blade.php", sprintf($viewLayout, 'Event Streams Dashboard', 'Pemantauan data aliran kejadian waktu nyata.'));
file_put_contents("$dir/resources/views/data-engineering/replay.blade.php", sprintf($viewLayout, 'Event Replay', 'Pemutaran ulang peristiwa masa lalu.'));
file_put_contents("$dir/resources/views/nlq/search.blade.php", sprintf($viewLayout, 'Pencarian Bahasa Alami', 'Lakukan kueri analitik menggunakan bahasa Indonesia.'));
file_put_contents("$dir/resources/views/nlq/dictionary.blade.php", sprintf($viewLayout, 'Kamus Pemetaan NLQ', 'Kelola pemetaan skema ke alias Indonesia.'));
file_put_contents("$dir/resources/views/report-scheduler/manager.blade.php", sprintf($viewLayout, 'Manajer Laporan Kondisional', 'Jadwalkan laporan analitik dengan kondisi tertentu.'));
file_put_contents("$dir/resources/views/report-scheduler/execution-logs.blade.php", sprintf($viewLayout, 'Log Eksekusi Laporan', 'Riwayat eksekusi jadwal laporan.'));
file_put_contents("$dir/resources/views/data-quality/health-dashboard.blade.php", sprintf($viewLayout, 'Dasbor Kualitas Data', 'Tinjau skor kesehatan dan integritas data sistem.'));
file_put_contents("$dir/resources/views/data-quality/resolution-center.blade.php", sprintf($viewLayout, 'Pusat Resolusi Anomali', 'Penyelesaian manual untuk data yang tidak wajar.'));
@mkdir("$dir/resources/views/report-scheduler", 0777, true);
file_put_contents("$dir/resources/views/report-scheduler/create.blade.php", sprintf($viewLayout, 'Buat Laporan Baru', 'Form pembuatan laporan kondisional.'));

// 6. Seeder
file_put_contents("$dir/database/seeders/DataEngineeringSeeder.php", "<?php
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
        \$admin = User::first() ?? User::factory()->create();

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
            'user_id' => \$admin->id,
            'natural_query' => 'tampilkan siswa dengan nilai rata-rata tertinggi',
            'generated_sql' => 'SELECT * FROM students ORDER BY gpa DESC LIMIT 10',
        ]);

        \$report = ConditionalReport::create([
            'report_name' => 'Laporan Nilai Anjlok',
            'view_template' => 'reports.low_grades',
            'cron_schedule' => '0 0 * * 0',
            'distribution_list' => ['kepsek@example.com'],
            'trigger_condition_sql' => 'SELECT COUNT(*) FROM students WHERE gpa < 2.0',
            'created_by' => \$admin->id,
        ]);

        ReportScheduleLog::create([
            'conditional_report_id' => \$report->id,
            'status' => 'skipped',
        ]);

        \$rule = DataQualityRule::create([
            'target_table' => 'students',
            'target_column' => 'nisn',
            'rule_type' => 'format',
            'rule_definition' => \'/^[0-9]{10}$/\',
        ]);

        AnomalyRecord::create([
            'data_quality_rule_id' => \$rule->id,
            'record_id' => '100',
            'invalid_data' => ['nisn' => 'ABC12345'],
            'status' => 'detected',
        ]);
    }
}
");

// 7. Routes
file_put_contents("$dir/routes/data_engineering.php", "<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataEngineering\EventStreamingController;
use App\Http\Controllers\DataEngineering\NlqController;
use App\Http\Controllers\DataEngineering\ConditionalReportController;
use App\Http\Controllers\DataEngineering\DataQualityController;

Route::middleware(['auth'])->group(function () {
    Route::get('/data-engineering/streams', [EventStreamingController::class, 'monitorDashboard'])->name('data_engineering.streams');
    Route::get('/data-engineering/replay', [EventStreamingController::class, 'streamLogs'])->name('data_engineering.replay');
    
    Route::get('/nlq/search', [NlqController::class, 'searchInterface'])->name('nlq.search');
    Route::post('/nlq/search', [NlqController::class, 'executeQuery']);
    Route::get('/nlq/dictionary', [NlqController::class, 'dictionaryMapping'])->name('nlq.dictionary');

    Route::get('/report-scheduler/manager', [ConditionalReportController::class, 'index'])->name('report_scheduler.manager');
    Route::get('/report-scheduler/create', [ConditionalReportController::class, 'create'])->name('report_scheduler.create');
    Route::post('/report-scheduler/store', [ConditionalReportController::class, 'store'])->name('report_scheduler.store');
    Route::get('/report-scheduler/logs', [ConditionalReportController::class, 'logs'])->name('report_scheduler.logs');
    Route::post('/report-scheduler/{id}/trigger', [ConditionalReportController::class, 'manualTrigger'])->name('report_scheduler.trigger');

    Route::get('/data-quality/health', [DataQualityController::class, 'healthDashboard'])->name('data_quality.health_dashboard');
    Route::get('/data-quality/resolution', [DataQualityController::class, 'resolutionCenter'])->name('data_quality.resolution_center');
    Route::post('/data-quality/anomaly/{id}/resolve', [DataQualityController::class, 'resolveAnomaly'])->name('data_quality.resolve_anomaly');
});
");

// Require in web.php
$webPhpPath = "$dir/routes/web.php";
$webPhp = file_get_contents($webPhpPath);
if (strpos($webPhp, "require __DIR__.'/data_engineering.php';") === false) {
    $webPhp = str_replace("require __DIR__.'/auth.php';", "require __DIR__.'/data_engineering.php';\nrequire __DIR__.'/auth.php';", $webPhp);
    file_put_contents($webPhpPath, $webPhp);
}

// 8. Test
file_put_contents("$dir/tests/Feature/DataEngineering/DataEngineeringSuiteTest.php", "<?php
namespace Tests\Feature\DataEngineering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class DataEngineeringSuiteTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        \$this->user = User::factory()->create(['role' => 'admin'] ?? []);
    }

    public function test_can_access_event_streams() {
        \$this->actingAs(\$this->user)->get(route('data_engineering.streams'))->assertStatus(200);
    }
    public function test_can_access_nlq_search() {
        \$this->actingAs(\$this->user)->get(route('nlq.search'))->assertStatus(200);
    }
    public function test_can_access_report_manager() {
        \$this->actingAs(\$this->user)->get(route('report_scheduler.manager'))->assertStatus(200);
    }
    public function test_can_access_data_quality_dashboard() {
        \$this->actingAs(\$this->user)->get(route('data_quality.health_dashboard'))->assertStatus(200);
    }
}
");

echo "DONE\n";
