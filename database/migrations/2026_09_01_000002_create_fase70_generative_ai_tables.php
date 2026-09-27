<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_tutor_sessions')) {
            Schema::create('ai_tutor_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('subject_name', 128);
                $table->string('topic', 255);
                $table->string('difficulty_level', 32)->default('intermediate'); // beginner, intermediate, advanced
                $table->text('learning_objective')->nullable();
                $table->string('status', 32)->default('active'); // active, completed
                $table->timestamps();

                $table->index(['student_id', 'status']);
            });
        }

        if (! Schema::hasTable('ai_tutor_messages')) {
            Schema::create('ai_tutor_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('ai_tutor_sessions')->cascadeOnDelete();
                $table->string('sender', 16); // student, ai
                $table->text('message');
                $table->string('socratic_scaffold_type', 64)->nullable(); // prompt_question, hint, praise, conceptual_bridge
                $table->decimal('comprehension_score', 4, 2)->nullable();
                $table->timestamps();

                $table->index('session_id');
            });
        }

        if (! Schema::hasTable('essay_submissions')) {
            Schema::create('essay_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('assignment_title', 255);
                $table->text('essay_text');
                $table->decimal('ai_score', 5, 2)->nullable(); // 0.00 - 100.00
                $table->decimal('originality_score', 5, 2)->nullable(); // 0.00 - 100.00
                $table->decimal('argument_depth_score', 5, 2)->nullable();
                $table->decimal('coherence_score', 5, 2)->nullable();
                $table->text('ai_feedback')->nullable();
                $table->json('plagiarism_matches')->nullable();
                $table->timestamps();

                $table->index('student_id');
            });
        }

        if (! Schema::hasTable('generated_lesson_plans')) {
            Schema::create('generated_lesson_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->string('subject_name', 128);
                $table->string('grade_level', 32);
                $table->string('topic', 255);
                $table->string('curriculum_type', 64)->default('Kurikulum Merdeka');
                $table->json('learning_objectives');
                $table->json('pedagogical_steps');
                $table->json('assessment_rubric');
                $table->string('status', 32)->default('draft'); // draft, approved, published
                $table->timestamps();

                $table->index('teacher_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_lesson_plans');
        Schema::dropIfExists('essay_submissions');
        Schema::dropIfExists('ai_tutor_messages');
        Schema::dropIfExists('ai_tutor_sessions');
    }
};
