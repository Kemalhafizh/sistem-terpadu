<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audio Recorder & Live Waveform Canvas untuk Tahsin/Tahfidz.
     * A student's recitation recording, routed to their homeroom teacher for
     * review, plus timestamp-pinned tajwid feedback notes on that recording.
     */
    public function up(): void
    {
        Schema::create('tahsin_submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            // Resolved from the student's homeroom teacher at submission time
            // (see Classroom::teacher()) — nullable because a student without
            // an assigned classroom teacher can still record, just without a
            // reviewer yet.
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('surah_name');
            $table->unsignedInteger('start_ayah')->nullable();
            $table->unsignedInteger('end_ayah')->nullable();
            $table->string('audio_path');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['student_id']);
        });

        Schema::create('tahsin_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahsin_submission_id')->constrained('tahsin_submissions')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            // Second into the recording this note is pinned to — matches the
            // Android waveform's tap-to-annotate timestamp.
            $table->unsignedInteger('timestamp_seconds');
            $table->text('note');
            // e.g. "makhraj", "ghunnah", "mad", "waqaf" — free-form category
            // the teacher can optionally tag, not a fixed taxonomy yet.
            $table->string('tajwid_category')->nullable();
            $table->timestamps();

            $table->index(['tahsin_submission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahsin_annotations');
        Schema::dropIfExists('tahsin_submissions');
    }
};
