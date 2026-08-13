<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('foundation_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code');
            $table->string('unit_name');
            $table->enum('level', ['tk', 'sd', 'smp', 'sma', 'smk', 'universitas']);
            $table->string('address')->nullable();
            $table->string('principal_name')->nullable();
            $table->integer('student_count')->default(0);
            $table->integer('teacher_count')->default(0);
            $table->decimal('annual_budget', 15, 2)->default(0);
            $table->decimal('annual_revenue', 15, 2)->default(0);
            $table->boolean('is_flagship')->default(false);
            $table->timestamps();
        });
        Schema::create('foundation_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('report_type', ['keuangan_konsolidasi', 'akademik_konsolidasi', 'sdm_konsolidasi', 'sarana_konsolidasi', 'keislaman_konsolidasi', 'rapat_dewan']);
            $table->enum('period', ['monthly', 'quarterly', 'semester', 'annual']);
            $table->date('period_start');
            $table->date('period_end');
            $table->json('data_payload');
            $table->text('executive_summary')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved']);
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
        Schema::create('foundation_board_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('meeting_date');
            $table->time('start_time')->nullable();
            $table->text('agenda');
            $table->text('minutes')->nullable();
            $table->json('attendees')->nullable();
            $table->json('decisions')->nullable();
            $table->json('action_items')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed']);
            $table->foreignId('secretary_id')->nullable()->constrained('users');
            $table->timestamps();
        });
        Schema::create('wakaf_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code');
            $table->string('asset_name');
            $table->enum('asset_type', ['tanah', 'bangunan', 'kendaraan', 'peralatan', 'investasi', 'kas_produktif', 'lainnya']);
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->decimal('acquisition_value', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->date('acquisition_date');
            $table->string('wakif_name');
            $table->enum('utilization_status', ['produktif', 'idle', 'maintenance', 'planned']);
            $table->text('utilization_description')->nullable();
            $table->decimal('annual_income', 15, 2)->default(0);
            $table->string('certificate_number')->nullable();
            $table->timestamps();
        });
        Schema::create('wakaf_utilization_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wakaf_asset_id')->constrained('wakaf_assets');
            $table->year('report_year');
            $table->decimal('income_generated', 15, 2)->default(0);
            $table->decimal('maintenance_cost', 15, 2)->default(0);
            $table->decimal('net_benefit', 15, 2)->default(0);
            $table->text('impact_description')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->timestamps();
            $table->unique(['wakaf_asset_id', 'report_year']);
        });
        Schema::create('staff_rotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('from_unit_id')->constrained('foundation_units');
            $table->foreignId('to_unit_id')->constrained('foundation_units');
            $table->enum('rotation_type', ['mutasi_tetap', 'penugasan_sementara', 'pertukaran', 'promosi']);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->text('reason');
            $table->enum('status', ['proposed', 'approved', 'active', 'completed', 'cancelled']);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
        Schema::create('foundation_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_unit_id')->nullable()->constrained('foundation_units');
            $table->string('kpi_name');
            $table->enum('category', ['akademik', 'keuangan', 'sdm', 'sarana', 'keislaman', 'reputasi']);
            $table->decimal('target_value', 10, 2);
            $table->decimal('actual_value', 10, 2);
            $table->string('unit');
            $table->year('fiscal_year');
            $table->timestamps();
        });
        Schema::create('unit_accreditation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_unit_id')->constrained('foundation_units');
            $table->string('accreditation_body');
            $table->string('accreditation_grade');
            $table->decimal('accreditation_score', 5, 2)->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->string('certificate_number')->nullable();
            $table->enum('status', ['active', 'expired', 'renewal_in_progress']);
            $table->timestamps();
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
