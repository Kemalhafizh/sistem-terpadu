<?php
$baseDir = "c:/project/portofolio/project-super-web/sistem-terpadu";

function writeFile($path, $content) {
    global $baseDir;
    $fullPath = $baseDir . '/' . $path;
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($fullPath, trim($content));
    echo "Created: $path\n";
}

// 1. Migration
writeFile("database/migrations/2026_10_26_000000_create_advanced_hr_tables.php", <<< 'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('position_title');
            $table->string('department');
            $table->text('requirements');
            $table->enum('status', ['open', 'closed', 'hold'])->default('open');
            $table->date('closing_date');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('job_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('resume_url')->nullable();
            $table->enum('pipeline_stage', ['applied', 'screened', 'interview', 'micro_teaching', 'offered', 'rejected'])->default('applied');
            $table->json('assessment_scores')->nullable();
            $table->timestamps();
        });

        Schema::create('compensation_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('base_salary', 15, 2);
            $table->json('allowances')->nullable();
            $table->json('deductions')->nullable();
            $table->date('effective_date');
            $table->timestamps();
        });

        Schema::create('benefit_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('claim_type', ['medical', 'travel', 'training', 'other']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->string('receipt_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('wellness_programs', function (Blueprint $table) {
            $table->id();
            $table->string('program_name');
            $table->enum('category', ['physical_health', 'mental_health', 'financial_wellness']);
            $table->text('description');
            $table->integer('points_reward')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('wellness_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('wellness_program_id')->constrained('wellness_programs')->cascadeOnDelete();
            $table->date('participation_date');
            $table->text('feedback_note')->nullable();
            $table->timestamps();
        });

        Schema::create('exit_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('interviewer_id')->constrained('users')->cascadeOnDelete();
            $table->date('interview_date');
            $table->json('reasons_for_leaving')->nullable();
            $table->text('constructive_feedback')->nullable();
            $table->enum('status', ['scheduled', 'completed'])->default('scheduled');
            $table->timestamps();
        });

        Schema::create('turnover_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->integer('year');
            $table->integer('month');
            $table->integer('headcount_start');
            $table->integer('terminations');
            $table->decimal('turnover_rate', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnover_analytics');
        Schema::dropIfExists('exit_interviews');
        Schema::dropIfExists('wellness_logs');
        Schema::dropIfExists('wellness_programs');
        Schema::dropIfExists('benefit_claims');
        Schema::dropIfExists('compensation_structures');
        Schema::dropIfExists('job_applicants');
        Schema::dropIfExists('job_vacancies');
    }
};
PHP);

// 2. Models
writeFile("app/Models/JobVacancy.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_title', 'department', 'requirements', 'status', 'closing_date', 'created_by'
    ];

    protected $casts = [
        'closing_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applicants()
    {
        return $this->hasMany(JobApplicant::class);
    }
}
PHP);

writeFile("app/Models/JobApplicant.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_vacancy_id', 'full_name', 'email', 'resume_url', 'pipeline_stage', 'assessment_scores'
    ];

    protected $casts = [
        'assessment_scores' => 'array',
    ];

    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }
}
PHP);

writeFile("app/Models/CompensationStructure.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompensationStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'base_salary', 'allowances', 'deductions', 'effective_date'
    ];

    protected $casts = [
        'allowances' => 'array',
        'deductions' => 'array',
        'effective_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
PHP);

writeFile("app/Models/BenefitClaim.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenefitClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'claim_type', 'amount', 'description', 'receipt_url', 'status', 'approved_by'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
PHP);

writeFile("app/Models/WellnessProgram.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WellnessProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_name', 'category', 'description', 'points_reward', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(WellnessLog::class);
    }
}
PHP);

writeFile("app/Models/WellnessLog.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WellnessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'wellness_program_id', 'participation_date', 'feedback_note'
    ];

    protected $casts = [
        'participation_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function program()
    {
        return $this->belongsTo(WellnessProgram::class, 'wellness_program_id');
    }
}
PHP);

writeFile("app/Models/ExitInterview.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitInterview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'interviewer_id', 'interview_date', 'reasons_for_leaving', 'constructive_feedback', 'status'
    ];

    protected $casts = [
        'reasons_for_leaving' => 'array',
        'interview_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
PHP);

writeFile("app/Models/TurnoverAnalytic.php", <<< 'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoverAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'department', 'year', 'month', 'headcount_start', 'terminations', 'turnover_rate'
    ];
}
PHP);

// 3. Services
writeFile("app/Services/HR/RecruitmentService.php", <<< 'PHP'
<?php

namespace App\Services\HR;

use App\Models\JobVacancy;
use App\Models\JobApplicant;

class RecruitmentService
{
    public function publishVacancy(array $data)
    {
        return JobVacancy::create($data);
    }

    public function movePipelineStage(JobApplicant $applicant, $newStage)
    {
        $applicant->update(['pipeline_stage' => $newStage]);
        return $applicant;
    }

    public function scheduleInterview(JobApplicant $applicant, $date)
    {
        // Implementation
    }

    public function generateOfferLetter(JobApplicant $applicant)
    {
        // Implementation
    }
}
PHP);

writeFile("app/Services/HR/CompensationService.php", <<< 'PHP'
<?php

namespace App\Services\HR;

use App\Models\CompensationStructure;
use App\Models\BenefitClaim;

class CompensationService
{
    public function calculateMonthlyPayroll($employeeId)
    {
        // Implementation
    }

    public function processClaim(BenefitClaim $claim, $status, $userId)
    {
        $claim->update([
            'status' => $status,
            'approved_by' => $userId
        ]);
        return $claim;
    }

    public function generatePayslipPdf($employeeId, $month, $year)
    {
        // Implementation
    }
}
PHP);

writeFile("app/Services/HR/WellnessService.php", <<< 'PHP'
<?php

namespace App\Services\HR;

use App\Models\WellnessProgram;
use App\Models\WellnessLog;

class WellnessService
{
    public function createProgram(array $data)
    {
        return WellnessProgram::create($data);
    }

    public function logParticipation(array $data)
    {
        return WellnessLog::create($data);
    }

    public function calculateWellnessScore($employeeId)
    {
        return WellnessLog::where('employee_id', $employeeId)
            ->join('wellness_programs', 'wellness_logs.wellness_program_id', '=', 'wellness_programs.id')
            ->sum('wellness_programs.points_reward');
    }
}
PHP);

writeFile("app/Services/HR/TurnoverService.php", <<< 'PHP'
<?php

namespace App\Services\HR;

use App\Models\ExitInterview;
use App\Models\TurnoverAnalytic;

class TurnoverService
{
    public function scheduleExitInterview(array $data)
    {
        return ExitInterview::create($data);
    }

    public function calculateTurnoverRate($department, $year, $month)
    {
        // Implementation
    }

    public function generateRetentionReport()
    {
        // Implementation
    }
}
PHP);

// 4. Controllers
writeFile("app/Http/Controllers/HR/RecruitmentController.php", <<< 'PHP'
<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobVacancy;
use App\Models\JobApplicant;

class RecruitmentController extends Controller
{
    public function vacancies()
    {
        $vacancies = JobVacancy::all();
        return view('recruitment.vacancies', compact('vacancies'));
    }

    public function createVacancy()
    {
        return view('recruitment.vacancy-form');
    }

    public function storeVacancy(Request $request)
    {
        // Validation and storage logic
        return redirect()->route('recruitment.vacancies');
    }

    public function applicants()
    {
        //
    }

    public function kanban()
    {
        $applicants = JobApplicant::all();
        return view('recruitment.kanban', compact('applicants'));
    }

    public function updateStage(Request $request, $id)
    {
        //
    }
}
PHP);

writeFile("app/Http/Controllers/HR/CompensationController.php", <<< 'PHP'
<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompensationStructure;
use App\Models\BenefitClaim;

class CompensationController extends Controller
{
    public function dashboard()
    {
        return view('compensation.dashboard');
    }

    public function structures()
    {
        $structures = CompensationStructure::all();
        return view('compensation.employee-structures', compact('structures'));
    }

    public function storeStructure(Request $request)
    {
        //
    }

    public function claims()
    {
        $claims = BenefitClaim::all();
        return view('compensation.claims-approval', compact('claims'));
    }

    public function createClaim()
    {
        return view('compensation.claim-form');
    }

    public function storeClaim(Request $request)
    {
        //
    }

    public function approveClaim(Request $request, $id)
    {
        //
    }
}
PHP);

writeFile("app/Http/Controllers/HR/WellnessController.php", <<< 'PHP'
<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WellnessProgram;
use App\Models\WellnessLog;

class WellnessController extends Controller
{
    public function index()
    {
        return view('wellness.catalog');
    }

    public function programs()
    {
        //
    }

    public function createProgram()
    {
        //
    }

    public function storeProgram(Request $request)
    {
        //
    }

    public function myWellness()
    {
        return view('wellness.employee-dashboard');
    }

    public function logParticipation(Request $request)
    {
        //
    }

    public function hrAnalytics()
    {
        return view('wellness.hr-analytics');
    }
}
PHP);

writeFile("app/Http/Controllers/HR/TurnoverController.php", <<< 'PHP'
<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExitInterview;

class TurnoverController extends Controller
{
    public function dashboard()
    {
        return view('turnover.dashboard');
    }

    public function exitInterviews()
    {
        $interviews = ExitInterview::all();
        return view('turnover.exit-interviews', compact('interviews'));
    }

    public function scheduleInterview(Request $request)
    {
        //
    }

    public function storeInterview(Request $request)
    {
        //
    }

    public function analytics()
    {
        //
    }
}
PHP);

// 5. Views
writeFile("resources/views/recruitment/kanban.blade.php", <<< 'HTML'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Kanban Rekrutmen
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">SMA Islam Sultan Agung 1 Semarang</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Columns -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Pelamar Baru</h2>
            <!-- Cards -->
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Wawancara</h2>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Micro Teaching</h2>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Penawaran</h2>
        </div>
    </div>
</div>
@endsection
HTML);

writeFile("resources/views/recruitment/vacancies.blade.php", <<< 'HTML'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Lowongan Pekerjaan
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">SMA Islam Sultan Agung 1 Semarang</p>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Posisi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Departemen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                <!-- Rows -->
            </tbody>
        </table>
    </div>
</div>
@endsection
HTML);

writeFile("resources/views/recruitment/vacancy-form.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Form Lowongan Baru</h1>
</div>
@endsection
HTML);

writeFile("resources/views/recruitment/applicant-detail.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Detail Pelamar</h1>
</div>
@endsection
HTML);

writeFile("resources/views/compensation/dashboard.blade.php", <<< 'HTML'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Dasbor Kompensasi
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">SMA Islam Sultan Agung 1 Semarang</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Dashboard widgets -->
    </div>
</div>
@endsection
HTML);

writeFile("resources/views/compensation/employee-structures.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Struktur Kompensasi Karyawan</h1>
</div>
@endsection
HTML);

writeFile("resources/views/compensation/claims-approval.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Persetujuan Klaim</h1>
</div>
@endsection
HTML);

writeFile("resources/views/compensation/claim-form.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Form Pengajuan Klaim</h1>
</div>
@endsection
HTML);

writeFile("resources/views/wellness/catalog.blade.php", <<< 'HTML'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                Katalog Kesejahteraan
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">SMA Islam Sultan Agung 1 Semarang</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Programs -->
    </div>
</div>
@endsection
HTML);

writeFile("resources/views/wellness/employee-dashboard.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Dasbor Kesejahteraan Saya</h1>
</div>
@endsection
HTML);

writeFile("resources/views/wellness/hr-analytics.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Analitik Kesejahteraan SDM</h1>
</div>
@endsection
HTML);

writeFile("resources/views/turnover/dashboard.blade.php", <<< 'HTML'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Dasbor Turnover
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">SMA Islam Sultan Agung 1 Semarang</p>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <h2 class="font-bold text-gray-800 dark:text-gray-200 mb-4">Statistik Turnover</h2>
    </div>
</div>
@endsection
HTML);

writeFile("resources/views/turnover/exit-interviews.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Wawancara Keluar</h1>
</div>
@endsection
HTML);

writeFile("resources/views/turnover/clearance.blade.php", <<< 'HTML'
@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Penyelesaian Keluar</h1>
</div>
@endsection
HTML);

// 6. Seeder
writeFile("database/seeders/AdvancedHrSeeder.php", <<< 'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\JobVacancy;
use App\Models\JobApplicant;
use App\Models\CompensationStructure;
use App\Models\BenefitClaim;
use App\Models\WellnessProgram;
use App\Models\WellnessLog;
use App\Models\ExitInterview;
use App\Models\TurnoverAnalytic;

class AdvancedHrSeeder extends Seeder
{
    public function run()
    {
        $user = User::first() ?? User::factory()->create();
        $employee = Employee::first() ?? Employee::create(['user_id' => $user->id, 'employee_id' => 'EMP-001', 'position' => 'Teacher', 'department' => 'Academic', 'status' => 'active']);

        $vacancy = JobVacancy::create([
            'position_title' => 'Guru Matematika',
            'department' => 'Akademik',
            'requirements' => 'S1 Matematika, berpengalaman 2 tahun',
            'status' => 'open',
            'closing_date' => now()->addDays(30),
            'created_by' => $user->id
        ]);

        JobApplicant::create([
            'job_vacancy_id' => $vacancy->id,
            'full_name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'pipeline_stage' => 'applied'
        ]);

        CompensationStructure::create([
            'employee_id' => $employee->id,
            'base_salary' => 5000000.00,
            'allowances' => ['transport' => 500000],
            'deductions' => ['tax' => 250000],
            'effective_date' => now()
        ]);

        BenefitClaim::create([
            'employee_id' => $employee->id,
            'claim_type' => 'medical',
            'amount' => 1000000.00,
            'description' => 'Kacamata',
            'status' => 'pending'
        ]);

        $program = WellnessProgram::create([
            'program_name' => 'Senam Pagi Bersama',
            'category' => 'physical_health',
            'description' => 'Senam pagi setiap jumat',
            'points_reward' => 10,
            'is_active' => true
        ]);

        WellnessLog::create([
            'employee_id' => $employee->id,
            'wellness_program_id' => $program->id,
            'participation_date' => now(),
            'feedback_note' => 'Sangat menyegarkan'
        ]);

        ExitInterview::create([
            'employee_id' => $employee->id,
            'interviewer_id' => $user->id,
            'interview_date' => now()->addDays(10),
            'reasons_for_leaving' => ['career_change' => true],
            'status' => 'scheduled'
        ]);

        TurnoverAnalytic::create([
            'department' => 'Akademik',
            'year' => date('Y'),
            'month' => date('n'),
            'headcount_start' => 50,
            'terminations' => 1,
            'turnover_rate' => 2.00
        ]);
    }
}
PHP);

// 7. Routes
writeFile("routes/recruitment.php", <<< 'PHP'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HR\RecruitmentController;
use App\Http\Controllers\HR\CompensationController;
use App\Http\Controllers\HR\WellnessController;
use App\Http\Controllers\HR\TurnoverController;

Route::middleware(['auth'])->group(function () {
    Route::prefix('recruitment')->name('recruitment.')->group(function () {
        Route::get('/kanban', [RecruitmentController::class, 'kanban'])->name('kanban');
        Route::get('/vacancies', [RecruitmentController::class, 'vacancies'])->name('vacancies');
    });

    Route::prefix('compensation')->name('compensation.')->group(function () {
        Route::get('/dashboard', [CompensationController::class, 'dashboard'])->name('dashboard');
    });

    Route::prefix('wellness')->name('wellness.')->group(function () {
        Route::get('/', [WellnessController::class, 'index'])->name('index');
    });

    Route::prefix('turnover')->name('turnover.')->group(function () {
        Route::get('/dashboard', [TurnoverController::class, 'dashboard'])->name('dashboard');
    });
});
PHP);

// 8. Tests
writeFile("tests/Feature/HR/AdvancedHrSuiteTest.php", <<< 'PHP'
<?php

namespace Tests\Feature\HR;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdvancedHrSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_recruitment_kanban_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('recruitment.kanban'));
        $response->assertStatus(200);
    }

    public function test_compensation_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('compensation.dashboard'));
        $response->assertStatus(200);
    }

    public function test_wellness_catalog_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('wellness.index'));
        $response->assertStatus(200);
    }

    public function test_turnover_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('turnover.dashboard'));
        $response->assertStatus(200);
    }
}
PHP);

echo "All files generated successfully.\n";
?>
