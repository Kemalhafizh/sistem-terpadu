<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('irt_item_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->decimal('difficulty', 8, 4)->default(0);
            $table->decimal('discrimination', 8, 4)->default(1);
            $table->decimal('guessing', 8, 4)->default(0);
            $table->decimal('standard_error', 8, 4)->nullable();
            $table->integer('total_attempts')->default(0);
            $table->integer('correct_count')->default(0);
            $table->decimal('p_value', 5, 4)->nullable();
            $table->timestamp('last_calibrated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('irt_student_abilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->decimal('theta', 8, 4)->default(0);
            $table->decimal('standard_error', 8, 4)->nullable();
            $table->integer('items_attempted')->default(0);
            $table->timestamp('last_estimated_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id']);
        });

        Schema::create('cat_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('exam_id')->constrained();
            $table->decimal('current_theta', 8, 4)->default(0);
            $table->decimal('theta_se', 8, 4)->nullable();
            $table->integer('items_administered')->default(0);
            $table->integer('correct_count')->default(0);
            $table->enum('status', ['in_progress', 'completed', 'terminated'])->default('in_progress');
            $table->enum('termination_rule', ['max_items', 'se_threshold', 'time_limit', 'manual'])->nullable();
            $table->integer('max_items')->default(30);
            $table->decimal('se_threshold', 8, 4)->default(0.30);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cat_item_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cat_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained();
            $table->integer('sequence_number');
            $table->boolean('is_correct');
            $table->decimal('theta_before', 8, 4);
            $table->decimal('theta_after', 8, 4);
            $table->decimal('item_difficulty', 8, 4);
            $table->decimal('information_value', 8, 4)->nullable();
            $table->integer('response_time_seconds')->nullable();
            $table->timestamps();
        });

        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->string('title');
            $table->text('diagnosis')->nullable();
            $table->json('weak_competencies')->nullable();
            $table->json('recommended_resources')->nullable();
            $table->enum('status', ['generated', 'in_progress', 'completed'])->default('generated');
            $table->integer('progress_percentage')->default(0);
            $table->decimal('pre_score', 5, 2)->nullable();
            $table->decimal('post_score', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('learning_path_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['materi_bacaan', 'video', 'latihan_soal', 'kuis_mini', 'tugas_praktik']);
            $table->string('title');
            $table->text('content_reference')->nullable();
            $table->integer('estimated_minutes')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('competency_mastery_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->string('competency_code');
            $table->string('competency_description')->nullable();
            $table->enum('mastery_level', ['not_started', 'developing', 'approaching', 'proficient', 'advanced'])->default('not_started');
            $table->decimal('mastery_score', 5, 2)->default(0);
            $table->integer('attempts')->default(0);
            $table->timestamp('last_assessed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'competency_code'], 'cmr_unique_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('competency_mastery_records');
        Schema::dropIfExists('learning_path_items');
        Schema::dropIfExists('learning_paths');
        Schema::dropIfExists('cat_item_responses');
        Schema::dropIfExists('cat_sessions');
        Schema::dropIfExists('irt_student_abilities');
        Schema::dropIfExists('irt_item_parameters');
    }
};
