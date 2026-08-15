<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iep_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('case_manager_id')->constrained('employees')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('review_date');
            $table->json('diagnoses')->nullable();
            $table->text('present_level_of_performance');
            $table->json('annual_goals');
            $table->json('required_accommodations');
            $table->enum('status', ['draft', 'active', 'reviewed', 'closed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('screening_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('employees')->cascadeOnDelete();
            $table->string('screening_type');
            $table->json('responses');
            $table->integer('total_score');
            $table->enum('risk_level', ['low', 'moderate', 'high']);
            $table->text('system_recommendation');
            $table->boolean('requires_professional_referral')->default(false);
            $table->timestamps();
        });

        Schema::create('differentiated_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_plan_id')->nullable();
            $table->foreign('lesson_plan_id')->references('id')->on('lesson_plans')->nullOnDelete();
            $table->string('differentiation_type');
            $table->json('tier_1_strategy');
            $table->json('tier_2_strategy');
            $table->json('tier_3_strategy');
            $table->json('modified_assessments')->nullable();
            $table->text('teacher_reflection')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodation_registries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('accommodation_category');
            $table->string('specific_need');
            $table->boolean('is_active')->default(true);
            $table->json('applicable_contexts');
            $table->date('provision_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_registries');
        Schema::dropIfExists('differentiated_plans');
        Schema::dropIfExists('screening_results');
        Schema::dropIfExists('iep_documents');
    }
};
