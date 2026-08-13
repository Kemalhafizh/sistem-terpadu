<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

\ = <<< 'EOT'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('strategic_plans', function (Blueprint \) {
            \->id(); \->string('title'); \->text('vision'); \->text('mission');
            \->year('start_year'); \->year('end_year');
            \->enum('status', ['draft', 'approved', 'active', 'completed', 'archived'])->default('draft');
            \->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            \->timestamp('approved_at')->nullable(); \->timestamps();
        });
        Schema::create('strategic_objectives', function (Blueprint \) {
            \->id(); \->foreignId('strategic_plan_id')->constrained()->cascadeOnDelete();
            \->enum('perspective', ['pembelajaran_pertumbuhan', 'proses_internal', 'stakeholder', 'finansial']);
            \->string('code'); \->string('objective'); \->text('description')->nullable();
            \->integer('weight')->default(0); \->integer('sort_order')->default(0); \->timestamps();
        });
        Schema::create('key_performance_indicators', function (Blueprint \) {
            \->id(); \->foreignId('strategic_objective_id')->constrained()->cascadeOnDelete();
            \->string('code'); \->string('indicator'); \->string('unit');
            \->decimal('baseline_value', 10, 2); \->decimal('target_value', 10, 2); \->decimal('current_value', 10, 2)->default(0);
            \->enum('polarity', ['higher_is_better', 'lower_is_better']); \->enum('frequency', ['monthly', 'quarterly', 'semester', 'yearly']);
            \->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            \->text('data_source')->nullable(); \->text('formula')->nullable(); \->timestamps();
        });
        Schema::create('kpi_measurements', function (Blueprint \) {
            \->id(); \->foreignId('kpi_id')->constrained('key_performance_indicators')->cascadeOnDelete();
            \->date('measurement_date'); \->decimal('value', 10, 2);
            \->text('evidence')->nullable(); \->text('analysis')->nullable();
            \->foreignId('recorded_by')->constrained('users'); \->timestamps();
            \->unique(['kpi_id', 'measurement_date']);
        });
        Schema::create('annual_workplans', function (Blueprint \) {
            \->id(); \->foreignId('strategic_plan_id')->constrained()->cascadeOnDelete();
            \->string('title'); \->year('fiscal_year');
            \->enum('status', ['draft', 'submitted', 'approved', 'in_progress', 'completed'])->default('draft');
            \->decimal('total_budget', 15, 2)->default(0); \->decimal('total_realization', 15, 2)->default(0);
            \->foreignId('prepared_by')->constrained('users'); \->foreignId('approved_by')->nullable()->constrained('users'); \->timestamps();
        });
        Schema::create('workplan_programs', function (Blueprint \) {
            \->id(); \->foreignId('annual_workplan_id')->constrained()->cascadeOnDelete();
            \->foreignId('strategic_objective_id')->nullable()->constrained()->nullOnDelete();
            \->string('program_code'); \->string('program_name'); \->text('description')->nullable();
            \->enum('category', ['pengembangan_sdm', 'peningkatan_mutu_akademik', 'sarana_prasarana', 'kesiswaan', 'keislaman', 'humas_kerjasama', 'tata_kelola', 'lainnya']);
            \->decimal('budget_allocation', 15, 2)->default(0); \->decimal('realization', 15, 2)->default(0);
            \->enum('quarter', ['q1', 'q2', 'q3', 'q4', 'full_year']);
            \->foreignId('pic_user_id')->nullable()->constrained('users');
            \->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            \->integer('progress_percentage')->default(0); \->timestamps();
        });
        Schema::create('workplan_activities', function (Blueprint \) {
            \->id(); \->foreignId('program_id')->constrained('workplan_programs')->cascadeOnDelete();
            \->string('activity_name'); \->text('output_target');
            \->date('start_date')->nullable(); \->date('end_date')->nullable();
            \->decimal('budget', 15, 2)->default(0); \->decimal('realization', 15, 2)->default(0);
            \->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            \->text('evidence_notes')->nullable(); \->timestamps();
        });
        Schema::create('satisfaction_surveys', function (Blueprint \) {
            \->id(); \->string('title');
            \->enum('survey_type', ['ikm_siswa', 'ikm_orangtua', 'ikm_guru', 'ikm_mitra', 'custom']);
            \->text('description')->nullable(); \->date('start_date'); \->date('end_date');
            \->boolean('is_anonymous')->default(true);
            \->enum('status', ['draft', 'active', 'closed', 'analyzed'])->default('draft');
            \->foreignId('created_by')->constrained('users'); \->timestamps();
        });
        Schema::create('satisfaction_dimensions', function (Blueprint \) {
            \->id(); \->foreignId('survey_id')->constrained('satisfaction_surveys')->cascadeOnDelete();
            \->string('dimension_name'); \->integer('weight')->default(0); \->integer('sort_order')->default(0); \->timestamps();
        });
        Schema::create('satisfaction_questions', function (Blueprint \) {
            \->id(); \->foreignId('dimension_id')->constrained('satisfaction_dimensions')->cascadeOnDelete();
            \->text('question_text');
            \->enum('scale_type', ['likert_5', 'likert_7', 'yes_no', 'open_text']);
            \->integer('sort_order')->default(0); \->timestamps();
        });
        Schema::create('satisfaction_responses', function (Blueprint \) {
            \->id(); \->foreignId('survey_id')->constrained('satisfaction_surveys')->cascadeOnDelete();
            \->foreignId('question_id')->constrained('satisfaction_questions')->cascadeOnDelete();
            \->foreignId('respondent_id')->nullable()->constrained('users')->nullOnDelete();
            \->integer('score')->nullable(); \->text('text_response')->nullable(); \->timestamps();
        });
        Schema::create('swot_analyses', function (Blueprint \) {
            \->id(); \->foreignId('strategic_plan_id')->nullable()->constrained('strategic_plans')->nullOnDelete();
            \->string('title'); \->year('analysis_year');
            \->foreignId('created_by')->constrained('users'); \->text('conclusion')->nullable(); \->timestamps();
        });
        Schema::create('swot_items', function (Blueprint \) {
            \->id(); \->foreignId('swot_analysis_id')->constrained('swot_analyses')->cascadeOnDelete();
            \->enum('quadrant', ['strength', 'weakness', 'opportunity', 'threat']);
            \->text('description'); \->integer('impact_score')->default(0); \->integer('likelihood_score')->default(0);
            \->text('strategy_response')->nullable(); \->integer('sort_order')->default(0); \->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('swot_items'); Schema::dropIfExists('swot_analyses'); Schema::dropIfExists('satisfaction_responses');
        Schema::dropIfExists('satisfaction_questions'); Schema::dropIfExists('satisfaction_dimensions'); Schema::dropIfExists('satisfaction_surveys');
        Schema::dropIfExists('workplan_activities'); Schema::dropIfExists('workplan_programs'); Schema::dropIfExists('annual_workplans');
        Schema::dropIfExists('kpi_measurements'); Schema::dropIfExists('key_performance_indicators'); Schema::dropIfExists('strategic_objectives');
        Schema::dropIfExists('strategic_plans');
    }
};
EOT;
@mkdir('database/migrations', 0777, true);
file_put_contents('database/migrations/2026_10_05_000000_create_strategic_planning_tables.php', \);

// Routes file
file_put_contents('routes/strategic.php', "<?php\nuse Illuminate\Support\Facades\Route;\nuse App\Http\Controllers\Strategic\{StrategicPlanController, KpiController, AnnualWorkplanController, SatisfactionSurveyController, SwotAnalysisController};\n\nRoute::prefix('strategic')->name('strategic.')->group(function () {\n    Route::get('dashboard', [StrategicPlanController::class, 'dashboard'])->name('dashboard');\n    Route::resource('plan', StrategicPlanController::class);\n    Route::post('kpi/{id}/measure', [KpiController::class, 'storeMeasurement'])->name('kpi.measure');\n    Route::get('kpi/{id}/report', [KpiController::class, 'report'])->name('kpi.report');\n    Route::resource('workplan', AnnualWorkplanController::class);\n    Route::get('survey/{id}/respond', [SatisfactionSurveyController::class, 'respond'])->name('survey.respond');\n    Route::post('survey/{id}/respond', [SatisfactionSurveyController::class, 'storeResponse'])->name('survey.storeResponse');\n    Route::get('survey/{id}/results', [SatisfactionSurveyController::class, 'results'])->name('survey.results');\n    Route::resource('survey', SatisfactionSurveyController::class);\n    Route::resource('swot', SwotAnalysisController::class);\n});\n");

// Register route
\ = file_get_contents('routes/web.php');
if (strpos(\, 'routes/strategic.php') === false) {
    \ = str_replace(\"require __DIR__.'/auth.php';\", \"require __DIR__.'/strategic.php';\nrequire __DIR__.'/auth.php';\", \);
    file_put_contents('routes/web.php', \);
}

// Basic views
\ = [
    'plan/create.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Buat RENSTRA</h1> @endsection',
    'plan/show.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Detail RENSTRA</h1> @endsection',
    'kpi/input.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Input KPI</h1> @endsection',
    'kpi/report.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Laporan KPI</h1> @endsection',
    'workplan/index.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Daftar RKAS</h1> @endsection',
    'workplan/create.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Buat RKAS</h1> @endsection',
    'workplan/show.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Detail RKAS</h1> @endsection',
    'survey/index.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Daftar Survei IKM</h1> @endsection',
    'survey/create.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Buat Survei IKM</h1> @endsection',
    'survey/respond.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Isi Survei</h1> @endsection',
    'survey/results.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Hasil Survei IKM</h1> @endsection',
    'swot/index.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Daftar SWOT</h1> @endsection',
    'swot/create.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Buat SWOT</h1> @endsection',
    'swot/show.blade.php' => '@extends(\"layouts.app\") @section(\"content\") <h1>Detail SWOT</h1> @endsection',
];
foreach(\ as \ => \) {
    file_put_contents('resources/views/strategic/' . \, \);
}

// active scope
\ = file_get_contents('app/Models/StrategicPlan.php');
if(strpos(\, 'scopeActive') === false) {
    \ = str_replace('}', \"\n    public function scopeActive(\) { return \->where('status', 'active'); }\n}\", \);
    file_put_contents('app/Models/StrategicPlan.php', \);
}
