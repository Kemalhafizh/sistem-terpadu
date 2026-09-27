<?php

$basePath = 'c:/project/portofolio/project-super-web/sistem-terpadu';

$files = [
    'database/migrations/2026_11_03_000000_create_green_school_tables.php' => <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carbon_metrics', function (Blueprint \$table) {
            \$table->id();
            \$table->date('measurement_date');
            \$table->decimal('electricity_kwh', 10, 2);
            \$table->decimal('water_usage_m3', 10, 2);
            \$table->decimal('transport_emissions_kg', 10, 2);
            \$table->decimal('paper_usage_kg', 10, 2);
            \$table->decimal('total_carbon_footprint_kg', 12, 2);
            \$table->json('source_data')->nullable();
            \$table->text('reduction_initiatives')->nullable();
            \$table->timestamps();
        });

        Schema::create('waste_logs', function (Blueprint \$table) {
            \$table->id();
            \$table->date('log_date');
            \$table->string('waste_category');
            \$table->decimal('weight_kg', 8, 2);
            \$table->enum('disposal_method', ['recycled', 'composted', 'landfill', 'bank_sampah']);
            \$table->decimal('revenue_generated', 10, 2)->default(0);
            \$table->unsignedBigInteger('recorded_by');
            \$table->foreign('recorded_by')->references('id')->on('employees')->onDelete('cascade');
            \$table->timestamps();
        });

        Schema::create('eco_projects', function (Blueprint \$table) {
            \$table->id();
            \$table->unsignedBigInteger('subject_id')->nullable();
            \$table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            \$table->string('project_title');
            \$table->text('description');
            \$table->json('sdg_alignment')->nullable();
            \$table->date('start_date');
            \$table->date('end_date');
            \$table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
            \$table->json('participating_classes')->nullable();
            \$table->longText('impact_result')->nullable();
            \$table->timestamps();
        });

        Schema::create('eco_participations', function (Blueprint \$table) {
            \$table->id();
            \$table->unsignedBigInteger('student_id');
            \$table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            \$table->string('challenge_type');
            \$table->date('participation_date');
            \$table->integer('points_awarded')->default(0);
            \$table->text('evidence_url')->nullable();
            \$table->boolean('is_verified')->default(false);
            \$table->unsignedBigInteger('verified_by')->nullable();
            \$table->foreign('verified_by')->references('id')->on('employees')->nullOnDelete();
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('eco_participations');
        Schema::dropIfExists('eco_projects');
        Schema::dropIfExists('waste_logs');
        Schema::dropIfExists('carbon_metrics');
    }
};
EOT
    ,
    'app/Models/CarbonMetric.php' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarbonMetric extends Model
{
    use HasFactory;

    protected \$fillable = [
        'measurement_date',
        'electricity_kwh',
        'water_usage_m3',
        'transport_emissions_kg',
        'paper_usage_kg',
        'total_carbon_footprint_kg',
        'source_data',
        'reduction_initiatives'
    ];

    protected \$casts = [
        'measurement_date' => 'date',
        'source_data' => 'array',
        'electricity_kwh' => 'decimal:2',
        'water_usage_m3' => 'decimal:2',
        'transport_emissions_kg' => 'decimal:2',
        'paper_usage_kg' => 'decimal:2',
        'total_carbon_footprint_kg' => 'decimal:2',
    ];
}
EOT
    ,
    'app/Models/WasteLog.php' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    use HasFactory;

    protected \$fillable = [
        'log_date',
        'waste_category',
        'weight_kg',
        'disposal_method',
        'revenue_generated',
        'recorded_by'
    ];

    protected \$casts = [
        'log_date' => 'date',
        'weight_kg' => 'decimal:2',
        'revenue_generated' => 'decimal:2',
    ];

    public function recorder()
    {
        return \$this->belongsTo(Employee::class, 'recorded_by');
    }
}
EOT
    ,
    'app/Models/EcoProject.php' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcoProject extends Model
{
    use HasFactory;

    protected \$fillable = [
        'subject_id',
        'project_title',
        'description',
        'sdg_alignment',
        'start_date',
        'end_date',
        'status',
        'participating_classes',
        'impact_result'
    ];

    protected \$casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sdg_alignment' => 'array',
        'participating_classes' => 'array',
    ];

    public function subject()
    {
        return \$this->belongsTo(Subject::class);
    }
}
EOT
    ,
    'app/Models/EcoParticipation.php' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcoParticipation extends Model
{
    use HasFactory;

    protected \$fillable = [
        'student_id',
        'challenge_type',
        'participation_date',
        'points_awarded',
        'evidence_url',
        'is_verified',
        'verified_by'
    ];

    protected \$casts = [
        'participation_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function student()
    {
        return \$this->belongsTo(Student::class);
    }

    public function verifier()
    {
        return \$this->belongsTo(Employee::class, 'verified_by');
    }
}
EOT
    ,
    'app/Services/Green/EnvironmentalImpactService.php' => <<<EOT
<?php

namespace App\Services\Green;

use App\Models\CarbonMetric;

class EnvironmentalImpactService
{
    public function calculateEmissions(\$data)
    {
        // Example implementation
        return \$data['electricity_kwh'] * 0.85 + \$data['transport_emissions_kg'];
    }

    public function aggregateTransportData()
    {
        // Example implementation
        return 0;
    }

    public function predictMonthlyUsage()
    {
        // Example implementation
        return 0;
    }

    public function generateReductionTargets()
    {
        // Example implementation
        return [];
    }
}
EOT
    ,
    'app/Services/Green/WasteTrackingService.php' => <<<EOT
<?php

namespace App\Services\Green;

use App\Models\WasteLog;

class WasteTrackingService
{
    public function calculateDiversionRate()
    {
        return 0;
    }

    public function processBankSampahTransactions()
    {
        return true;
    }

    public function generateWasteAudit()
    {
        return [];
    }
}
EOT
    ,
    'app/Services/Green/GreenCurriculumService.php' => <<<EOT
<?php

namespace App\Services\Green;

use App\Models\EcoProject;

class GreenCurriculumService
{
    public function mapSdgToCurriculum()
    {
        return [];
    }

    public function trackProjectCompletion()
    {
        return true;
    }

    public function calculateEcoLiteracyScore()
    {
        return 0;
    }
}
EOT
    ,
    'app/Services/Green/GreenGamificationService.php' => <<<EOT
<?php

namespace App\Services\Green;

use App\Models\EcoParticipation;

class GreenGamificationService
{
    public function awardEcoPoints()
    {
        return true;
    }

    public function validateEvidence()
    {
        return true;
    }

    public function updateLeaderboard()
    {
        return [];
    }
}
EOT
    ,
    'app/Http/Controllers/Green/CarbonMetricController.php' => <<<EOT
<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CarbonMetricController extends Controller
{
    public function index()
    {
        return view('fase59.carbon.dashboard');
    }

    public function storeDailyData(Request \$request)
    {
        // Implementation
        return redirect()->back()->with('success', 'Data tersimpan.');
    }

    public function calculateMonthly()
    {
        // Implementation
    }

    public function reports()
    {
        return view('fase59.carbon.reports');
    }
}
EOT
    ,
    'app/Http/Controllers/Green/WasteManagementController.php' => <<<EOT
<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WasteManagementController extends Controller
{
    public function index()
    {
        return view('fase59.waste.tracker');
    }

    public function storeLog(Request \$request)
    {
        // Implementation
        return redirect()->back()->with('success', 'Log sampah tersimpan.');
    }

    public function updateLog(Request \$request, \$id)
    {
        // Implementation
    }

    public function bankSampahSync()
    {
        return view('fase59.waste.bank-sampah');
    }
}
EOT
    ,
    'app/Http/Controllers/Green/EcoProjectController.php' => <<<EOT
<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EcoProjectController extends Controller
{
    public function index()
    {
        return view('fase59.green-curriculum.project-list');
    }

    public function create()
    {
        // Implementation
    }

    public function store(Request \$request)
    {
        // Implementation
    }

    public function show(\$id)
    {
        // Implementation
    }

    public function update(Request \$request, \$id)
    {
        // Implementation
    }

    public function destroy(\$id)
    {
        // Implementation
    }

    public function sdgMapping()
    {
        return view('fase59.green-curriculum.sdg-mapping');
    }
}
EOT
    ,
    'app/Http/Controllers/Green/EcoChallengeController.php' => <<<EOT
<?php

namespace App\Http\Controllers\Green;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EcoChallengeController extends Controller
{
    public function index()
    {
        return view('fase59.eco-challenge.challenges');
    }

    public function submitEvidence(Request \$request)
    {
        // Implementation
    }

    public function verifyParticipation(\$id)
    {
        // Implementation
    }

    public function leaderboard()
    {
        return view('fase59.eco-challenge.leaderboard');
    }
}
EOT
    ,
    'resources/views/fase59/carbon/dashboard.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                Dashboard Jejak Karbon
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Ringkasan emisi karbon dan inisiatif hijau.</p>
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/carbon/data-entry.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Entri Data Emisi
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/carbon/reports.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Laporan Emisi
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/waste/tracker.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Pelacak Sampah
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/waste/log-entry.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Entri Log Sampah
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/waste/bank-sampah.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Bank Sampah
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/green-curriculum/project-list.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Daftar Proyek Lingkungan
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/green-curriculum/sdg-mapping.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Pemetaan SDG
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/green-curriculum/impact-report.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            Laporan Dampak
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/eco-challenge/challenges.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            Tantangan Eco
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/eco-challenge/leaderboard.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Papan Peringkat Eco
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'resources/views/fase59/eco-challenge/submission.blade.php' => <<<'EOT'
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Kirim Bukti Partisipasi
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
    </div>
</div>
@endsection
EOT
    ,
    'routes/green_school.php' => <<<EOT
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Green\CarbonMetricController;
use App\Http\Controllers\Green\WasteManagementController;
use App\Http\Controllers\Green\EcoProjectController;
use App\Http\Controllers\Green\EcoChallengeController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('green-school')->group(function () {
        // Carbon Metrics
        Route::prefix('carbon')->name('carbon.')->group(function () {
            Route::get('/', [CarbonMetricController::class, 'index'])->name('dashboard');
            Route::post('/store', [CarbonMetricController::class, 'storeDailyData'])->name('store');
            Route::get('/reports', [CarbonMetricController::class, 'reports'])->name('reports');
        });

        // Waste Management
        Route::prefix('waste')->name('waste.')->group(function () {
            Route::get('/', [WasteManagementController::class, 'index'])->name('tracker');
            Route::post('/store', [WasteManagementController::class, 'storeLog'])->name('store');
            Route::get('/bank-sampah', [WasteManagementController::class, 'bankSampahSync'])->name('bank-sampah');
        });

        // Eco Projects
        Route::prefix('eco-projects')->name('eco_projects.')->group(function () {
            Route::get('/', [EcoProjectController::class, 'index'])->name('index');
            Route::get('/sdg', [EcoProjectController::class, 'sdgMapping'])->name('sdg');
        });

        // Eco Challenges
        Route::prefix('eco-challenges')->name('eco_challenges.')->group(function () {
            Route::get('/', [EcoChallengeController::class, 'index'])->name('index');
            Route::get('/leaderboard', [EcoChallengeController::class, 'leaderboard'])->name('leaderboard');
        });
    });
});
EOT
    ,
    'database/seeders/GreenSchoolSeeder.php' => <<<EOT
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CarbonMetric;
use App\Models\WasteLog;
use App\Models\EcoProject;
use App\Models\EcoParticipation;
use App\Models\Employee;
use App\Models\Student;

class GreenSchoolSeeder extends Seeder
{
    public function run()
    {
        \$employee = Employee::first() ?? Employee::factory()->create();
        \$student = Student::first() ?? Student::factory()->create();

        CarbonMetric::create([
            'measurement_date' => now(),
            'electricity_kwh' => 1500.50,
            'water_usage_m3' => 200.00,
            'transport_emissions_kg' => 450.75,
            'paper_usage_kg' => 30.00,
            'total_carbon_footprint_kg' => 2181.25,
            'source_data' => ['sensor_1' => 'active'],
            'reduction_initiatives' => 'Pengurangan penggunaan listrik saat siang hari.'
        ]);

        WasteLog::create([
            'log_date' => now(),
            'waste_category' => 'Kertas',
            'weight_kg' => 10.5,
            'disposal_method' => 'bank_sampah',
            'revenue_generated' => 15000,
            'recorded_by' => \$employee->id
        ]);

        EcoProject::create([
            'project_title' => 'Taman Vertikal',
            'description' => 'Pembuatan taman vertikal di area sekolah.',
            'sdg_alignment' => [13, 15],
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'status' => 'in_progress',
            'participating_classes' => ['10A', '10B']
        ]);

        EcoParticipation::create([
            'student_id' => \$student->id,
            'challenge_type' => 'Bawa Tumbler',
            'participation_date' => now(),
            'points_awarded' => 10,
            'is_verified' => true,
            'verified_by' => \$employee->id
        ]);
    }
}
EOT
    ,
    'tests/Feature/Green/GreenSchoolSuiteTest.php' => <<<EOT
<?php

namespace Tests\Feature\Green;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class GreenSchoolSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \$this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_carbon_dashboard_is_accessible()
    {
        \$response = \$this->actingAs(\$this->user)->get(route('carbon.dashboard'));
        \$response->assertStatus(200);
    }

    public function test_waste_tracker_is_accessible()
    {
        \$response = \$this->actingAs(\$this->user)->get(route('waste.tracker'));
        \$response->assertStatus(200);
    }

    public function test_eco_projects_index_is_accessible()
    {
        \$response = \$this->actingAs(\$this->user)->get(route('eco_projects.index'));
        \$response->assertStatus(200);
    }

    public function test_eco_challenges_leaderboard_is_accessible()
    {
        \$response = \$this->actingAs(\$this->user)->get(route('eco_challenges.leaderboard'));
        \$response->assertStatus(200);
    }
}
EOT
];

foreach ($files as $path => $content) {
    $fullPath = $basePath.'/'.$path;
    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($fullPath, $content);
    echo "Created: $path\n";
}
