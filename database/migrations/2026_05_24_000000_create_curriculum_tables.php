<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syllabi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('grade_level');
            $table->enum('semester', ['ganjil', 'genap']);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_path');
            $table->enum('status', ['draft', 'approved', 'revision_needed'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['subject_id', 'academic_year_id', 'grade_level', 'semester'], 'syllabus_unique_key');
            $table->index(['subject_id', 'academic_year_id']);
        });

        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('topic');
            $table->tinyInteger('week_number')->nullable();
            $table->text('competency_standards')->nullable();
            $table->text('learning_objectives');
            $table->text('learning_activities');
            $table->text('assessment_method')->nullable();
            $table->text('resources')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'revision_needed'])->default('draft');
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'subject_id', 'academic_year_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('syllabi');
    }
};
