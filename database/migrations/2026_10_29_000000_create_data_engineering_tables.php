<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_streams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('aggregate_type');
            $table->string('aggregate_id');
            $table->string('event_type');
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->integer('version')->default(1);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('nlq_query_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('natural_query');
            $table->text('generated_sql');
            $table->boolean('is_successful')->default(true);
            $table->decimal('execution_time_ms', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('nlq_schema_maps', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('column_name');
            $table->string('indonesian_alias');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('conditional_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_name');
            $table->string('view_template');
            $table->string('cron_schedule');
            $table->json('distribution_list');
            $table->text('trigger_condition_sql')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('report_schedule_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conditional_report_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['skipped', 'generated', 'failed']);
            $table->text('execution_note')->nullable();
            $table->timestamps();
        });

        Schema::create('data_quality_rules', function (Blueprint $table) {
            $table->id();
            $table->string('target_table');
            $table->string('target_column');
            $table->enum('rule_type', ['format', 'range', 'uniqueness', 'referential']);
            $table->string('rule_definition');
            $table->integer('severity_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('anomaly_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_quality_rule_id')->constrained()->cascadeOnDelete();
            $table->string('record_id');
            $table->json('invalid_data');
            $table->enum('status', ['detected', 'ignored', 'resolved'])->default('detected');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_records');
        Schema::dropIfExists('data_quality_rules');
        Schema::dropIfExists('report_schedule_logs');
        Schema::dropIfExists('conditional_reports');
        Schema::dropIfExists('nlq_schema_maps');
        Schema::dropIfExists('nlq_query_logs');
        Schema::dropIfExists('event_streams');
    }
};
