<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for CBT Enterprise Maximization (Fase 86).
     */
    public function up(): void
    {
        // 1. Lembar Upload Jawaban Esai / Hitungan Rumus
        Schema::create('cbt_essay_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_banks')->cascadeOnDelete();
            $table->string('file_path');
            $table->integer('file_size')->default(0); // in bytes
            $table->string('mime_type')->default('image/jpeg');
            $table->text('student_notes')->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id'], 'cbt_essay_attempt_q_unique');
        });

        // 2. Log Intervensi Pengawas (Proctor Interventions)
        Schema::create('cbt_proctor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('proctor_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type'); // 'warning_modal', 'force_submit', 'extend_time', 'unlock_exam'
            $table->string('reason')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_proctor_logs');
        Schema::dropIfExists('cbt_essay_submissions');
    }
};
