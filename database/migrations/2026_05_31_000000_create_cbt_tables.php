<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bank Soal
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_image')->nullable();
            $table->enum('type', ['multiple_choice', 'true_false', 'essay'])->default('multiple_choice');
            $table->json('options')->nullable(); // ["A" => "Opsi A", "B" => "Opsi B", ...]
            $table->string('correct_answer')->nullable(); // "A", "B", "true", "false"
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('competency_code')->nullable(); // KD 3.1
            $table->enum('grade_level', ['X', 'XI', 'XII']);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['subject_id', 'difficulty', 'grade_level']);
        });

        // 2. Jadwal / Data Ujian
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['uts', 'uas', 'ulangan_harian', 'try_out'])->default('ulangan_harian');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes');
            $table->integer('total_questions');
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->boolean('is_secure_mode')->default(true);
            $table->integer('max_violations')->default(3);
            $table->decimal('passing_score', 5, 2)->default(75.00);
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['subject_id', 'classroom_id', 'status']);
        });

        // 3. Exam Questions (Pivot)
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->timestamps();
        });

        // 4. Attempts (Jawaban & Log Pelanggaran)
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->json('answers')->nullable(); // {"q_1": "A", "q_2": "C"}
            $table->json('question_order')->nullable(); // array id soal yang diacak
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('correct_count')->default(0);
            $table->integer('wrong_count')->default(0);
            $table->integer('unanswered_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'graded', 'blocked_violation', 'timeout'])->default('in_progress');
            $table->integer('violation_count')->default(0);
            $table->json('violation_logs')->nullable(); // [{"time": "...", "type": "blur"}]
            $table->boolean('is_auto_submitted')->default(false);
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('question_banks');
    }
};
