<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('remedial_sessions')) {
            Schema::create('remedial_sessions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('daily_assessment_id')->constrained('daily_assessments')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('original_score', 5, 2)->default(0.00);
                $table->decimal('kkm', 5, 2)->default(75.00);
                $table->enum('remedial_type', ['ulang_ujian', 'tugas_tambahan', 'belajar_mandiri'])->default('tugas_tambahan');
                $table->text('remedial_material')->nullable();
                $table->decimal('remedial_score', 5, 2)->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'waived'])->default('pending');
                $table->timestamp('deadline')->nullable();
                $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
                $table->text('teacher_notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('remedial_sessions');
    }
};
