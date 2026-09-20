<?php

$base_dir = "c:\\project\\portofolio\\project-super-web\\sistem-terpadu";

function write_file(string $path, string $content): void {
    global $base_dir;
    $full_path = $base_dir . "\\" . str_replace("/", "\\", $path);
    $dir = dirname($full_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($full_path, trim($content) . "\n");
}

write_file("database/migrations/2026_10_21_000000_create_foundation_governance_tables.php", <<<EOT
<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('foundation_units', function (Blueprint \$table) {
            \$table->id();
            \$table->string('unit_code');
            \$table->string('unit_name');
            \$table->enum('level', ['tk', 'sd', 'smp', 'sma', 'smk', 'universitas']);
            \$table->string('address')->nullable();
            \$table->string('principal_name')->nullable();
            \$table->integer('student_count')->default(0);
            \$table->integer('teacher_count')->default(0);
            \$table->decimal('annual_budget', 15, 2)->default(0);
            \$table->decimal('annual_revenue', 15, 2)->default(0);
            \$table->boolean('is_flagship')->default(false);
            \$table->timestamps();
        });
        Schema::create('foundation_reports', function (Blueprint \$table) {
            \$table->id();
            \$table->string('title');
            \$table->enum('report_type', ['keuangan_konsolidasi', 'akademik_konsolidasi', 'sdm_konsolidasi', 'sarana_konsolidasi', 'keislaman_konsolidasi', 'rapat_dewan']);
            \$table->enum('period', ['monthly', 'quarterly', 'semester', 'annual']);
            \$table->date('period_start');
            \$table->date('period_end');
            \$table->json('data_payload');
            \$table->text('executive_summary')->nullable();
            \$table->enum('status', ['draft', 'submitted', 'reviewed', 'approved']);
            \$table->foreignId('prepared_by')->constrained('users');
            \$table->foreignId('reviewed_by')->nullable()->constrained('users');
            \$table->timestamps();
        });
        Schema::create('foundation_board_meetings', function (Blueprint \$table) {
            \$table->id();
            \$table->string('title');
            \$table->date('meeting_date');
            \$table->time('start_time')->nullable();
            \$table->text('agenda');
            \$table->text('minutes')->nullable();
            \$table->json('attendees')->nullable();
            \$table->json('decisions')->nullable();
            \$table->json('action_items')->nullable();
            \$table->enum('status', ['scheduled', 'in_progress', 'completed']);
            \$table->foreignId('secretary_id')->nullable()->constrained('users');
            \$table->timestamps();
        });
        Schema::create('wakaf_assets', function (Blueprint \$table) {
            \$table->id();
            \$table->string('asset_code');
            \$table->string('asset_name');
            \$table->enum('asset_type', ['tanah', 'bangunan', 'kendaraan', 'peralatan', 'investasi', 'kas_produktif', 'lainnya']);
            \$table->text('description')->nullable();
            \$table->string('location')->nullable();
            \$table->decimal('acquisition_value', 15, 2);
            \$table->decimal('current_value', 15, 2);
            \$table->date('acquisition_date');
            \$table->string('wakif_name');
            \$table->enum('utilization_status', ['produktif', 'idle', 'maintenance', 'planned']);
            \$table->text('utilization_description')->nullable();
            \$table->decimal('annual_income', 15, 2)->default(0);
            \$table->string('certificate_number')->nullable();
            \$table->timestamps();
        });
        Schema::create('wakaf_utilization_reports', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('wakaf_asset_id')->constrained('wakaf_assets');
            \$table->year('report_year');
            \$table->decimal('income_generated', 15, 2)->default(0);
            \$table->decimal('maintenance_cost', 15, 2)->default(0);
            \$table->decimal('net_benefit', 15, 2)->default(0);
            \$table->text('impact_description')->nullable();
            \$table->foreignId('reported_by')->constrained('users');
            \$table->timestamps();
            \$table->unique(['wakaf_asset_id', 'report_year']);
        });
        Schema::create('staff_rotations', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained('users');
            \$table->foreignId('from_unit_id')->constrained('foundation_units');
            \$table->foreignId('to_unit_id')->constrained('foundation_units');
            \$table->enum('rotation_type', ['mutasi_tetap', 'penugasan_sementara', 'pertukaran', 'promosi']);
            \$table->date('effective_date');
            \$table->date('end_date')->nullable();
            \$table->text('reason');
            \$table->enum('status', ['proposed', 'approved', 'active', 'completed', 'cancelled']);
            \$table->foreignId('approved_by')->nullable()->constrained('users');
            \$table->timestamps();
        });
        Schema::create('foundation_kpis', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('foundation_unit_id')->nullable()->constrained('foundation_units');
            \$table->string('kpi_name');
            \$table->enum('category', ['akademik', 'keuangan', 'sdm', 'sarana', 'keislaman', 'reputasi']);
            \$table->decimal('target_value', 10, 2);
            \$table->decimal('actual_value', 10, 2);
            \$table->string('unit');
            \$table->year('fiscal_year');
            \$table->timestamps();
        });
        Schema::create('unit_accreditation_records', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('foundation_unit_id')->constrained('foundation_units');
            \$table->string('accreditation_body');
            \$table->string('accreditation_grade');
            \$table->decimal('accreditation_score', 5, 2)->nullable();
            \$table->date('valid_from');
            \$table->date('valid_until');
            \$table->string('certificate_number')->nullable();
            \$table->enum('status', ['active', 'expired', 'renewal_in_progress']);
            \$table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('unit_accreditation_records');
        Schema::dropIfExists('foundation_kpis');
        Schema::dropIfExists('staff_rotations');
        Schema::dropIfExists('wakaf_utilization_reports');
        Schema::dropIfExists('wakaf_assets');
        Schema::dropIfExists('foundation_board_meetings');
        Schema::dropIfExists('foundation_reports');
        Schema::dropIfExists('foundation_units');
    }
};
EOT
);

write_file("app/Models/FoundationUnit.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class FoundationUnit extends Model {
    use HasFactory;
    protected \$fillable = ['unit_code', 'unit_name', 'level', 'address', 'principal_name', 'student_count', 'teacher_count', 'annual_budget', 'annual_revenue', 'is_flagship'];
    protected \$casts = ['is_flagship' => 'boolean', 'annual_budget' => 'decimal:2', 'annual_revenue' => 'decimal:2'];
}
EOT
);
write_file("app/Models/FoundationReport.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class FoundationReport extends Model {
    use HasFactory;
    protected \$fillable = ['title', 'report_type', 'period', 'period_start', 'period_end', 'data_payload', 'executive_summary', 'status', 'prepared_by', 'reviewed_by'];
    protected \$casts = ['data_payload' => 'array', 'period_start' => 'date', 'period_end' => 'date'];
}
EOT
);
write_file("app/Models/FoundationBoardMeeting.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class FoundationBoardMeeting extends Model {
    use HasFactory;
    protected \$fillable = ['title', 'meeting_date', 'start_time', 'agenda', 'minutes', 'attendees', 'decisions', 'action_items', 'status', 'secretary_id'];
    protected \$casts = ['meeting_date' => 'date', 'attendees' => 'array', 'decisions' => 'array', 'action_items' => 'array'];
}
EOT
);
write_file("app/Models/WakafAsset.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class WakafAsset extends Model {
    use HasFactory;
    protected \$fillable = ['asset_code', 'asset_name', 'asset_type', 'description', 'location', 'acquisition_value', 'current_value', 'acquisition_date', 'wakif_name', 'utilization_status', 'utilization_description', 'annual_income', 'certificate_number'];
    protected \$casts = ['acquisition_date' => 'date', 'acquisition_value' => 'decimal:2', 'current_value' => 'decimal:2', 'annual_income' => 'decimal:2'];
}
EOT
);
write_file("app/Models/WakafUtilizationReport.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class WakafUtilizationReport extends Model {
    use HasFactory;
    protected \$fillable = ['wakaf_asset_id', 'report_year', 'income_generated', 'maintenance_cost', 'net_benefit', 'impact_description', 'reported_by'];
    protected \$casts = ['income_generated' => 'decimal:2', 'maintenance_cost' => 'decimal:2', 'net_benefit' => 'decimal:2'];
}
EOT
);
write_file("app/Models/StaffRotation.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class StaffRotation extends Model {
    use HasFactory;
    protected \$fillable = ['user_id', 'from_unit_id', 'to_unit_id', 'rotation_type', 'effective_date', 'end_date', 'reason', 'status', 'approved_by'];
    protected \$casts = ['effective_date' => 'date', 'end_date' => 'date'];
}
EOT
);
write_file("app/Models/FoundationKpi.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class FoundationKpi extends Model {
    use HasFactory;
    protected \$fillable = ['foundation_unit_id', 'kpi_name', 'category', 'target_value', 'actual_value', 'unit', 'fiscal_year'];
    protected \$casts = ['target_value' => 'decimal:2', 'actual_value' => 'decimal:2'];
}
EOT
);
write_file("app/Models/UnitAccreditationRecord.php", <<<EOT
<?php
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
class UnitAccreditationRecord extends Model {
    use HasFactory;
    protected \$fillable = ['foundation_unit_id', 'accreditation_body', 'accreditation_grade', 'accreditation_score', 'valid_from', 'valid_until', 'certificate_number', 'status'];
    protected \$casts = ['valid_from' => 'date', 'valid_until' => 'date', 'accreditation_score' => 'decimal:2'];
}
EOT
);

write_file("app/Services/Foundation/FoundationGovernanceService.php", <<<EOT
<?php
namespace App\\Services\\Foundation;
use App\\Models\\FoundationUnit;
use App\\Models\\FoundationReport;
use App\\Models\\FoundationBoardMeeting;
use App\\Models\\WakafAsset;
use App\\Models\\FoundationKpi;
class FoundationGovernanceService {
    public function getConsolidatedFinancials(): array {
        return ['budget' => FoundationUnit::sum('annual_budget'), 'revenue' => FoundationUnit::sum('annual_revenue')];
    }
    public function getConsolidatedStudentStats(): array {
        return ['total_students' => FoundationUnit::sum('student_count'), 'total_teachers' => FoundationUnit::sum('teacher_count')];
    }
    public function getConsolidatedAcademicPerformance(): array {
        return ['avg_score' => 85.5, 'graduates' => 1200];
    }
    public function generateBoardReport(string \$type, array \$period): FoundationReport {
        return FoundationReport::create(['title' => 'Report '.\$type, 'report_type' => \$type, 'period' => 'monthly', 'period_start' => \$period['start'], 'period_end' => \$period['end'], 'data_payload' => [], 'status' => 'draft', 'prepared_by' => 1]);
    }
    public function getActionItemStatus(): \\Illuminate\\Support\\Collection {
        return collect([]);
    }
    public function getWakafPortfolioSummary(): array {
        return ['total_value' => WakafAsset::sum('current_value'), 'total_income' => WakafAsset::sum('annual_income')];
    }
    public function getKpiScorecard(int \$year): \\Illuminate\\Support\\Collection {
        return FoundationKpi::where('fiscal_year', \$year)->get();
    }
}
EOT
);

write_file("app/Http/Controllers/Foundation/FoundationController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Services\\Foundation\\FoundationGovernanceService;
class FoundationController extends Controller {
    public function dashboard(FoundationGovernanceService \$service) {
        return view('foundation.dashboard', [
            'financials' => \$service->getConsolidatedFinancials(),
            'students' => \$service->getConsolidatedStudentStats()
        ]);
    }
}
EOT
);

write_file("app/Http/Controllers/Foundation/FoundationUnitController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Models\\FoundationUnit;
use Illuminate\\Http\\Request;
class FoundationUnitController extends Controller {
    public function index() { return view('foundation.units.index', ['units' => FoundationUnit::all()]); }
    public function show(\$id) { return view('foundation.units.show', ['unit' => FoundationUnit::findOrFail(\$id)]); }
    public function create() { return view('foundation.units.create'); }
    public function store(Request \$request) { FoundationUnit::create(\$request->all()); return redirect()->route('foundation.units.index'); }
}
EOT
);

write_file("app/Http/Controllers/Foundation/FoundationReportController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Models\\FoundationReport;
use Illuminate\\Http\\Request;
class FoundationReportController extends Controller {
    public function index() { return view('foundation.reports.index', ['reports' => FoundationReport::all()]); }
    public function create() { return view('foundation.reports.create'); }
    public function store(Request \$request) { FoundationReport::create(\$request->all()); return redirect()->route('foundation.reports.index'); }
    public function show(\$id) { return view('foundation.reports.show', ['report' => FoundationReport::findOrFail(\$id)]); }
}
EOT
);

write_file("app/Http/Controllers/Foundation/FoundationMeetingController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Models\\FoundationBoardMeeting;
use Illuminate\\Http\\Request;
class FoundationMeetingController extends Controller {
    public function index() { return view('foundation.meetings.index', ['meetings' => FoundationBoardMeeting::all()]); }
    public function create() { return view('foundation.meetings.create'); }
    public function store(Request \$request) { FoundationBoardMeeting::create(\$request->all()); return redirect()->route('foundation.meetings.index'); }
    public function show(\$id) { return view('foundation.meetings.show', ['meeting' => FoundationBoardMeeting::findOrFail(\$id)]); }
}
EOT
);

write_file("app/Http/Controllers/Foundation/WakafAssetController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Models\\WakafAsset;
use Illuminate\\Http\\Request;
class WakafAssetController extends Controller {
    public function index() { return view('foundation.wakaf.index', ['assets' => WakafAsset::all()]); }
    public function create() { return view('foundation.wakaf.create'); }
    public function store(Request \$request) { WakafAsset::create(\$request->all()); return redirect()->route('foundation.wakaf.index'); }
    public function show(\$id) { return view('foundation.wakaf.show', ['asset' => WakafAsset::findOrFail(\$id)]); }
    public function utilization(\$id) { return view('foundation.wakaf.utilization', ['asset' => WakafAsset::findOrFail(\$id)]); }
}
EOT
);

write_file("app/Http/Controllers/Foundation/StaffRotationController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Models\\StaffRotation;
use Illuminate\\Http\\Request;
class StaffRotationController extends Controller {
    public function index() { return view('foundation.rotation.index', ['rotations' => StaffRotation::all()]); }
    public function create() { return view('foundation.rotation.create'); }
    public function store(Request \$request) { StaffRotation::create(\$request->all()); return redirect()->route('foundation.rotation.index'); }
    public function show(\$id) { return view('foundation.rotation.show', ['rotation' => StaffRotation::findOrFail(\$id)]); }
}
EOT
);

write_file("app/Http/Controllers/Foundation/FoundationKpiController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Models\\FoundationKpi;
class FoundationKpiController extends Controller {
    public function scorecard() { return view('foundation.kpi.scorecard', ['kpis' => FoundationKpi::all()]); }
}
EOT
);

write_file("app/Http/Controllers/Foundation/AccreditationTrackerController.php", <<<EOT
<?php
namespace App\\Http\\Controllers\\Foundation;
use App\\Http\\Controllers\\Controller;
use App\\Models\\UnitAccreditationRecord;
use Illuminate\\Http\\Request;
class AccreditationTrackerController extends Controller {
    public function index() { return view('foundation.accreditation.index', ['records' => UnitAccreditationRecord::all()]); }
    public function create() { return view('foundation.accreditation.create'); }
    public function store(Request \$request) { UnitAccreditationRecord::create(\$request->all()); return redirect()->route('foundation.accreditation.index'); }
    public function show(\$id) { return view('foundation.accreditation.detail', ['record' => UnitAccreditationRecord::findOrFail(\$id)]); }
}
EOT
);

$blades = [
    'dashboard' => "Dashboard Foundation",
    'units/index' => "Unit Foundation",
    'units/show' => "Detail Unit",
    'units/create' => "Tambah Unit",
    'reports/index' => "Daftar Laporan",
    'reports/create' => "Buat Laporan",
    'reports/show' => "Detail Laporan",
    'meetings/index' => "Daftar Rapat",
    'meetings/show' => "Detail Rapat",
    'meetings/create' => "Buat Rapat",
    'wakaf/index' => "Aset Wakaf",
    'wakaf/show' => "Detail Wakaf",
    'wakaf/create' => "Tambah Wakaf",
    'wakaf/utilization' => "Utilisasi Wakaf",
    'rotation/index' => "Rotasi Staf",
    'rotation/create' => "Buat Rotasi",
    'kpi/scorecard' => "KPI Scorecard",
    'accreditation/index' => "Akreditasi",
    'accreditation/detail' => "Detail Akreditasi"
];

foreach ($blades as $blade => $title) {
    write_file("resources/views/foundation/{$blade}.blade.php", <<<EOT
@extends('layouts.app')
@section('content')
<div class="rounded-3xl card border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    <div class="flex items-center space-x-2 mb-4">
        <svg class="w-6 h-6 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{\$title} SMA Islam Sultan Agung 1 Semarang</h1>
    </div>
    <div class="text-sm text-gray-600 dark:text-gray-300 divide-y">
        <div class="py-2">
            <p class="font-bold text-emerald-600 dark:text-emerald-400">Konten {\$title}</p>
        </div>
    </div>
</div>
@endsection
EOT
    );
}

write_file("database/seeders/FoundationGovernanceSeeder.php", <<<EOT
<?php
namespace Database\\Seeders;
use Illuminate\\Database\\Seeder;
use App\\Models\\FoundationUnit;
use App\\Models\\FoundationReport;
use App\\Models\\FoundationBoardMeeting;
use App\\Models\\WakafAsset;
use App\\Models\\StaffRotation;
use App\\Models\\FoundationKpi;
use App\\Models\\UnitAccreditationRecord;
use App\\Models\\User;
class FoundationGovernanceSeeder extends Seeder {
    public function run() {
        \$user = User::first() ?? User::factory()->create();
        \$tk = FoundationUnit::create(['unit_code' => 'TK-01', 'unit_name' => 'TK ISA', 'level' => 'tk', 'student_count' => 120, 'is_flagship' => false]);
        \$sd = FoundationUnit::create(['unit_code' => 'SD-01', 'unit_name' => 'SD ISA', 'level' => 'sd', 'student_count' => 450, 'is_flagship' => false]);
        \$smp = FoundationUnit::create(['unit_code' => 'SMP-01', 'unit_name' => 'SMP ISA 1', 'level' => 'smp', 'student_count' => 600, 'is_flagship' => false]);
        \$sma = FoundationUnit::create(['unit_code' => 'SMA-01', 'unit_name' => 'SMA ISA 1', 'level' => 'sma', 'student_count' => 1200, 'is_flagship' => true]);
        \$univ = FoundationUnit::create(['unit_code' => 'UNIV-01', 'unit_name' => 'Universitas UNISSULA', 'level' => 'universitas', 'student_count' => 8000, 'is_flagship' => false]);
        FoundationReport::create(['title' => 'Lap Keu', 'report_type' => 'keuangan_konsolidasi', 'period' => 'monthly', 'period_start' => now(), 'period_end' => now(), 'data_payload' => [], 'status' => 'draft', 'prepared_by' => \$user->id]);
        FoundationReport::create(['title' => 'Lap Akad', 'report_type' => 'akademik_konsolidasi', 'period' => 'monthly', 'period_start' => now(), 'period_end' => now(), 'data_payload' => [], 'status' => 'draft', 'prepared_by' => \$user->id]);
        FoundationBoardMeeting::create(['title' => 'Rapat', 'meeting_date' => now(), 'agenda' => 'Agenda 1', 'status' => 'completed']);
        WakafAsset::create(['asset_code' => 'WKF-01', 'asset_name' => 'Tanah 1', 'asset_type' => 'tanah', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-02', 'asset_name' => 'Tanah 2', 'asset_type' => 'tanah', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-03', 'asset_name' => 'Bangunan 1', 'asset_type' => 'bangunan', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-04', 'asset_name' => 'Bangunan 2', 'asset_type' => 'bangunan', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-05', 'asset_name' => 'Mobil 1', 'asset_type' => 'kendaraan', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        StaffRotation::create(['user_id' => \$user->id, 'from_unit_id' => \$tk->id, 'to_unit_id' => \$sd->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => now(), 'reason' => 'test', 'status' => 'active']);
        StaffRotation::create(['user_id' => \$user->id, 'from_unit_id' => \$sd->id, 'to_unit_id' => \$smp->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => now(), 'reason' => 'test', 'status' => 'completed']);
        StaffRotation::create(['user_id' => \$user->id, 'from_unit_id' => \$smp->id, 'to_unit_id' => \$sma->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => now(), 'reason' => 'test', 'status' => 'proposed']);
        for (\$i=0; \$i<10; \$i++) {
            FoundationKpi::create(['kpi_name' => 'KPI '.\$i, 'category' => 'akademik', 'target_value' => 100, 'actual_value' => 90, 'unit' => '%', 'fiscal_year' => 2026]);
        }
        foreach([\$tk, \$sd, \$smp, \$sma, \$univ] as \$unit) {
            UnitAccreditationRecord::create(['foundation_unit_id' => \$unit->id, 'accreditation_body' => 'BAN', 'accreditation_grade' => 'A', 'valid_from' => now(), 'valid_until' => now()->addYears(5), 'status' => 'active']);
        }
    }
}
EOT
);

write_file("routes/foundation.php", <<<EOT
<?php
use Illuminate\\Support\\Facades\\Route;
use App\\Http\\Controllers\\Foundation\\FoundationController;
use App\\Http\\Controllers\\Foundation\\FoundationUnitController;
use App\\Http\\Controllers\\Foundation\\FoundationReportController;
use App\\Http\\Controllers\\Foundation\\FoundationMeetingController;
use App\\Http\\Controllers\\Foundation\\WakafAssetController;
use App\\Http\\Controllers\\Foundation\\StaffRotationController;
use App\\Http\\Controllers\\Foundation\\FoundationKpiController;
use App\\Http\\Controllers\\Foundation\\AccreditationTrackerController;
Route::middleware(['auth'])->group(function () {
    Route::get('/foundation/dashboard', [FoundationController::class, 'dashboard'])->name('foundation.dashboard');
    Route::resource('/foundation/units', FoundationUnitController::class, ['as' => 'foundation']);
    Route::resource('/foundation/reports', FoundationReportController::class, ['as' => 'foundation']);
    Route::resource('/foundation/meetings', FoundationMeetingController::class, ['as' => 'foundation']);
    Route::get('/foundation/wakaf/{wakaf}/utilization', [WakafAssetController::class, 'utilization'])->name('foundation.wakaf.utilization');
    Route::resource('/foundation/wakaf', WakafAssetController::class, ['as' => 'foundation']);
    Route::resource('/foundation/rotation', StaffRotationController::class, ['as' => 'foundation']);
    Route::get('/foundation/kpi/scorecard', [FoundationKpiController::class, 'scorecard'])->name('foundation.kpi.scorecard');
    Route::resource('/foundation/accreditation', AccreditationTrackerController::class, ['as' => 'foundation']);
});
EOT
);

write_file("tests/Feature/Foundation/FoundationGovernanceTest.php", <<<EOT
<?php
namespace Tests\\Feature\\Foundation;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;
use App\\Models\\User;
class FoundationGovernanceTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void {
        parent::setUp();
        \$this->actingAs(User::factory()->create());
    }
    public function test_can_view_foundation_dashboard() {
        \$response = \$this->get('/foundation/dashboard');
        \$response->assertStatus(200);
    }
    public function test_can_view_foundation_units_index() {
        \$response = \$this->get('/foundation/units');
        \$response->assertStatus(200);
    }
    public function test_can_view_wakaf_assets_index() {
        \$response = \$this->get('/foundation/wakaf');
        \$response->assertStatus(200);
    }
    public function test_can_view_kpi_scorecard() {
        \$response = \$this->get('/foundation/kpi/scorecard');
        \$response->assertStatus(200);
    }
}
EOT
);
