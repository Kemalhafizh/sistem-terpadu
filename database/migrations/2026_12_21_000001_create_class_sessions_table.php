<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 117.1 — Sesi Kelas Hidup (Live Class Session).
 *
 * One row per (schedule, date): a teacher "starts" a scheduled lesson, the
 * students scan a rotating QR, and the session is ended by the teacher or
 * auto-closed by the scheduler.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            // Nullable + nullOnDelete: deleting a timetable entry must not
            // wipe (or be blocked by) the attendance history taught under it.
            // classroom/subject/teacher are copied below for that reason.
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('classroom_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->date('session_date');
            $table->unsignedTinyInteger('jam_ke');
            $table->time('scheduled_start');
            $table->time('scheduled_end');
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled', 'auto_closed'])->default('scheduled');
            // Per-session HMAC key for the rotating QR (see ClassSessionService::qrTokenFor).
            // Replaces the blueprint's qr_token/qr_token_expires_at pair: the token is
            // derived per 30-second window, so any number of teacher devices show the
            // same QR and polling never writes to the database.
            $table->string('qr_secret', 64)->nullable();
            $table->string('topic')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_auto_closed')->default(false);
            $table->foreignId('teaching_journal_id')->nullable()->constrained('teaching_journals')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['schedule_id', 'session_date'], 'uniq_session_per_schedule_date');
            $table->index(['teacher_id', 'session_date']);
            $table->index(['classroom_id', 'session_date']);
            $table->index(['status', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
