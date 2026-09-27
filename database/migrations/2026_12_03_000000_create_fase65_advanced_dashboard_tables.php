<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 65.1: Executive Command Center KPIs
        if (! Schema::hasTable('executive_kpis')) {
            Schema::create('executive_kpis', function (Blueprint $table) {
                $table->id();
                $table->string('metric_name');
                $table->string('category'); // academic, finance, attendance, discipline, operations
                $table->decimal('current_value', 15, 2);
                $table->decimal('target_value', 15, 2)->nullable();
                $table->decimal('previous_value', 15, 2)->nullable();
                $table->string('trend_direction')->default('neutral'); // up, down, neutral
                $table->string('unit')->default(''); // %, Rp, Siswa, Guru
                $table->timestamp('calculated_at');
                $table->timestamps();
            });
        }

        // 65.2: Teacher Performance 360 Dashboard Metrics
        if (! Schema::hasTable('teacher_360_metrics')) {
            Schema::create('teacher_360_metrics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
                $table->decimal('student_satisfaction_score', 5, 2)->default(0);
                $table->decimal('peer_review_score', 5, 2)->default(0);
                $table->decimal('avg_class_grade', 5, 2)->default(0);
                $table->integer('attendance_percentage')->default(100);
                $table->json('skill_radar_data')->nullable(); // pedagogy, leadership, professional, personality, social
                $table->timestamps();
            });
        }

        // 65.3: Student Progress Cockpit Snapshots
        if (! Schema::hasTable('student_progress_snapshots')) {
            Schema::create('student_progress_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
                $table->json('academic_timeline')->nullable();
                $table->json('competency_radar')->nullable();
                $table->integer('overall_percentile')->default(50);
                $table->string('at_risk_status')->default('none'); // none, low, medium, high
                $table->timestamps();
            });
        }

        // 65.4: Financial Health Scoreboard Metrics
        if (! Schema::hasTable('financial_health_metrics')) {
            Schema::create('financial_health_metrics', function (Blueprint $table) {
                $table->id();
                $table->date('report_date');
                $table->decimal('cash_reserve', 15, 2)->default(0);
                $table->decimal('monthly_burn_rate', 15, 2)->default(0);
                $table->decimal('projected_revenue', 15, 2)->default(0);
                $table->decimal('fee_collection_rate', 5, 2)->default(0); // in percentage
                $table->json('expense_distribution')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_health_metrics');
        Schema::dropIfExists('student_progress_snapshots');
        Schema::dropIfExists('teacher_360_metrics');
        Schema::dropIfExists('executive_kpis');
    }
};
