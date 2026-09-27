<?php

$migration = <<< 'EOT'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('strategic_plans', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->text('vision'); $table->text('mission');
            $table->year('start_year'); $table->year('end_year');
            $table->enum('status', ['draft', 'approved', 'active', 'completed', 'archived'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable(); $table->timestamps();
        });
        Schema::create('strategic_objectives', function (Blueprint $table) {
            $table->id(); $table->foreignId('strategic_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('perspective', ['pembelajaran_pertumbuhan', 'proses_internal', 'stakeholder', 'finansial']);
            $table->string('code'); $table->string('objective'); $table->text('description')->nullable();
            $table->integer('weight')->default(0); $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('key_performance_indicators', function (Blueprint $table) {
            $table->id(); $table->foreignId('strategic_objective_id')->constrained()->cascadeOnDelete();
            $table->string('code'); $table->string('indicator'); $table->string('unit');
            $table->decimal('baseline_value', 10, 2); $table->decimal('target_value', 10, 2); $table->decimal('current_value', 10, 2)->default(0);
            $table->enum('polarity', ['higher_is_better', 'lower_is_better']); $table->enum('frequency', ['monthly', 'quarterly', 'semester', 'yearly']);
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('data_source')->nullable(); $table->text('formula')->nullable(); $table->timestamps();
        });
        Schema::create('kpi_measurements', function (Blueprint $table) {
            $table->id(); $table->foreignId('kpi_id')->constrained('key_performance_indicators')->cascadeOnDelete();
            $table->date('measurement_date'); $table->decimal('value', 10, 2);
            $table->text('evidence')->nullable(); $table->text('analysis')->nullable();
            $table->foreignId('recorded_by')->constrained('users'); $table->timestamps();
            $table->unique(['kpi_id', 'measurement_date']);
        });
        Schema::create('annual_workplans', function (Blueprint $table) {
            $table->id(); $table->foreignId('strategic_plan_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->year('fiscal_year');
            $table->enum('status', ['draft', 'submitted', 'approved', 'in_progress', 'completed'])->default('draft');
            $table->decimal('total_budget', 15, 2)->default(0); $table->decimal('total_realization', 15, 2)->default(0);
            $table->foreignId('prepared_by')->constrained('users'); $table->foreignId('approved_by')->nullable()->constrained('users'); $table->timestamps();
        });
        Schema::create('workplan_programs', function (Blueprint $table) {
            $table->id(); $table->foreignId('annual_workplan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strategic_objective_id')->nullable()->constrained()->nullOnDelete();
            $table->string('program_code'); $table->string('program_name'); $table->text('description')->nullable();
            $table->enum('category', ['pengembangan_sdm', 'peningkatan_mutu_akademik', 'sarana_prasarana', 'kesiswaan', 'keislaman', 'humas_kerjasama', 'tata_kelola', 'lainnya']);
            $table->decimal('budget_allocation', 15, 2)->default(0); $table->decimal('realization', 15, 2)->default(0);
            $table->enum('quarter', ['q1', 'q2', 'q3', 'q4', 'full_year']);
            $table->foreignId('pic_user_id')->nullable()->constrained('users');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->integer('progress_percentage')->default(0); $table->timestamps();
        });
        Schema::create('workplan_activities', function (Blueprint $table) {
            $table->id(); $table->foreignId('program_id')->constrained('workplan_programs')->cascadeOnDelete();
            $table->string('activity_name'); $table->text('output_target');
            $table->date('start_date')->nullable(); $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0); $table->decimal('realization', 15, 2)->default(0);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->text('evidence_notes')->nullable(); $table->timestamps();
        });
        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id(); $table->string('title');
            $table->enum('survey_type', ['ikm_siswa', 'ikm_orangtua', 'ikm_guru', 'ikm_mitra', 'custom']);
            $table->text('description')->nullable(); $table->date('start_date'); $table->date('end_date');
            $table->boolean('is_anonymous')->default(true);
            $table->enum('status', ['draft', 'active', 'closed', 'analyzed'])->default('draft');
            $table->foreignId('created_by')->constrained('users'); $table->timestamps();
        });
        Schema::create('satisfaction_dimensions', function (Blueprint $table) {
            $table->id(); $table->foreignId('survey_id')->constrained('satisfaction_surveys')->cascadeOnDelete();
            $table->string('dimension_name'); $table->integer('weight')->default(0); $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('satisfaction_questions', function (Blueprint $table) {
            $table->id(); $table->foreignId('dimension_id')->constrained('satisfaction_dimensions')->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('scale_type', ['likert_5', 'likert_7', 'yes_no', 'open_text']);
            $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('satisfaction_responses', function (Blueprint $table) {
            $table->id(); $table->foreignId('survey_id')->constrained('satisfaction_surveys')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('satisfaction_questions')->cascadeOnDelete();
            $table->foreignId('respondent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('score')->nullable(); $table->text('text_response')->nullable(); $table->timestamps();
        });
        Schema::create('swot_analyses', function (Blueprint $table) {
            $table->id(); $table->foreignId('strategic_plan_id')->nullable()->constrained('strategic_plans')->nullOnDelete();
            $table->string('title'); $table->year('analysis_year');
            $table->foreignId('created_by')->constrained('users'); $table->text('conclusion')->nullable(); $table->timestamps();
        });
        Schema::create('swot_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('swot_analysis_id')->constrained('swot_analyses')->cascadeOnDelete();
            $table->enum('quadrant', ['strength', 'weakness', 'opportunity', 'threat']);
            $table->text('description'); $table->integer('impact_score')->default(0); $table->integer('likelihood_score')->default(0);
            $table->text('strategy_response')->nullable(); $table->integer('sort_order')->default(0); $table->timestamps();
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
file_put_contents('database/migrations/2026_10_05_000000_create_strategic_planning_tables.php', $migration);

// Routes file
file_put_contents('routes/strategic.php', "<?php\nuse Illuminate\Support\Facades\Route;\nuse App\Http\Controllers\Strategic\{StrategicPlanController, KpiController, AnnualWorkplanController, SatisfactionSurveyController, SwotAnalysisController};\n\nRoute::prefix('strategic')->name('strategic.')->group(function () {\n    Route::get('dashboard', [StrategicPlanController::class, 'dashboard'])->name('dashboard');\n    Route::resource('plan', StrategicPlanController::class);\n    Route::post('kpi/{id}/measure', [KpiController::class, 'storeMeasurement'])->name('kpi.measure');\n    Route::get('kpi/{id}/report', [KpiController::class, 'report'])->name('kpi.report');\n    Route::resource('workplan', AnnualWorkplanController::class);\n    Route::get('survey/{id}/respond', [SatisfactionSurveyController::class, 'respond'])->name('survey.respond');\n    Route::post('survey/{id}/respond', [SatisfactionSurveyController::class, 'storeResponse'])->name('survey.storeResponse');\n    Route::get('survey/{id}/results', [SatisfactionSurveyController::class, 'results'])->name('survey.results');\n    Route::resource('survey', SatisfactionSurveyController::class);\n    Route::resource('swot', SwotAnalysisController::class);\n});\n");

// Register route
$web = file_get_contents('routes/web.php');
if (strpos($web, 'routes/strategic.php') === false) {
    $web = str_replace("require __DIR__.'/auth.php';", "require __DIR__.'/strategic.php';\nrequire __DIR__.'/auth.php';", $web);
    file_put_contents('routes/web.php', $web);
}

// Basic views
$views = [
    'plan/create.blade.php' => '@extends("layouts.app") @section("content") <h1>Buat RENSTRA</h1> @endsection',
    'plan/show.blade.php' => '@extends("layouts.app") @section("content") <h1>Detail RENSTRA</h1> @endsection',
    'kpi/input.blade.php' => '@extends("layouts.app") @section("content") <h1>Input KPI</h1> @endsection',
    'kpi/report.blade.php' => '@extends("layouts.app") @section("content") <h1>Laporan KPI</h1> @endsection',
    'workplan/index.blade.php' => '@extends("layouts.app") @section("content") <h1>Daftar RKAS</h1> @endsection',
    'workplan/create.blade.php' => '@extends("layouts.app") @section("content") <h1>Buat RKAS</h1> @endsection',
    'workplan/show.blade.php' => '@extends("layouts.app") @section("content") <h1>Detail RKAS</h1> @endsection',
    'survey/index.blade.php' => '@extends("layouts.app") @section("content") <h1>Daftar Survei IKM</h1> @endsection',
    'survey/create.blade.php' => '@extends("layouts.app") @section("content") <h1>Buat Survei IKM</h1> @endsection',
    'survey/respond.blade.php' => '@extends("layouts.app") @section("content") <h1>Isi Survei</h1> @endsection',
    'survey/results.blade.php' => '@extends("layouts.app") @section("content") <h1>Hasil Survei IKM</h1> @endsection',
    'swot/index.blade.php' => '@extends("layouts.app") @section("content") <h1>Daftar SWOT</h1> @endsection',
    'swot/create.blade.php' => '@extends("layouts.app") @section("content") <h1>Buat SWOT</h1> @endsection',
    'swot/show.blade.php' => '@extends("layouts.app") @section("content") <h1>Detail SWOT</h1> @endsection',
];
foreach ($views as $file => $content) {
    file_put_contents('resources/views/strategic/'.$file, $content);
}

// active scope
if (file_exists('app/Models/StrategicPlan.php')) {
    $model = file_get_contents('app/Models/StrategicPlan.php');
    if (strpos($model, 'scopeActive') === false) {
        $model = str_replace('}', "\n    public function scopeActive(\$query) { return \$query->where('status', 'active'); }\n}", $model);
        file_put_contents('app/Models/StrategicPlan.php', $model);
    }
}
