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