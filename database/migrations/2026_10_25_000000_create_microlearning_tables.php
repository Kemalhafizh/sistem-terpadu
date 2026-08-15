<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up()
    {
        Schema::create('micro_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->nullable()->constrained('syllabi')->nullOnDelete();
            $table->string('title');
            $table->text('ai_generated_summary')->nullable();
            $table->integer('estimated_minutes')->default(5);
            $table->boolean('is_published')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('micro_lesson_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('micro_lesson_id')->constrained('micro_lessons')->cascadeOnDelete();
            $table->integer('sequence_order');
            $table->enum('card_type', ['text', 'video', 'interactive', 'quiz']);
            $table->text('content');
            $table->json('ai_prompts_used')->nullable();
            $table->timestamps();
        });

        Schema::create('gamified_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('join_code')->unique();
            $table->enum('status', ['draft', 'waiting', 'live', 'finished'])->default('draft');
            $table->json('game_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gamified_quiz_id')->constrained('gamified_quizzes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_score')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('streak_count')->default(0);
            $table->timestamps();
        });

        Schema::create('memory_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->text('front_content');
            $table->text('back_content');
            $table->integer('difficulty_level')->default(1);
            $table->timestamps();
        });

        Schema::create('spaced_repetition_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('memory_card_id')->constrained('memory_cards')->cascadeOnDelete();
            $table->integer('repetition_count')->default(0);
            $table->decimal('easiness_factor', 5, 2)->default(2.50);
            $table->integer('interval_days')->default(1);
            $table->date('next_review_date');
            $table->timestamps();
        });

        Schema::create('digital_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('transcript_hash')->unique();
            $table->json('competency_radar')->nullable();
            $table->text('verification_url')->nullable();
            $table->enum('status', ['draft', 'verified', 'revoked'])->default('draft');
            $table->timestamps();
        });

        Schema::create('micro_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->json('awarding_criteria')->nullable();
            $table->timestamps();
        });

        Schema::create('micro_student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('micro_badge_id')->constrained('micro_badges')->cascadeOnDelete();
            $table->date('awarded_at');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('evidence_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_student_badges');
        Schema::dropIfExists('micro_badges');
        Schema::dropIfExists('digital_transcripts');
        Schema::dropIfExists('spaced_repetition_logs');
        Schema::dropIfExists('memory_cards');
        Schema::dropIfExists('quiz_leaderboards');
        Schema::dropIfExists('gamified_quizzes');
        Schema::dropIfExists('micro_lesson_cards');
        Schema::dropIfExists('micro_lessons');
    }
};