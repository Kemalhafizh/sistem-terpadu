<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 117.2 — Presensi per sesi kelas (per jam pelajaran).
 *
 * Separate from the daily `attendances` table, which keeps working as-is.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained();
            $table->enum('status', ['hadir', 'alpha', 'sakit', 'izin', 'telat'])->default('alpha');
            $table->enum('check_in_method', ['qr_scan', 'manual_teacher', 'auto_alpha'])->default('auto_alpha');
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_override')->default(false);
            $table->foreignId('override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('override_at')->nullable();
            $table->string('override_reason')->nullable();
            $table->timestamps();

            $table->unique(['class_session_id', 'student_id'], 'uniq_student_per_session');
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_attendances');
    }
};
