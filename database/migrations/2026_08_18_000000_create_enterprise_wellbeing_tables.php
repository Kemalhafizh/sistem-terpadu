<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wellbeing_screenings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('screening_type', ['srq20', 'phq9', 'custom'])->default('srq20');
            $table->string('academic_year', 9);
            $table->enum('semester', ['odd', 'even'])->default('odd');
            $table->string('target_grade')->nullable();
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('wellbeing_screening_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wellbeing_screening_id')->constrained('wellbeing_screenings')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->json('responses');
            $table->integer('total_score');
            $table->enum('risk_level', ['low', 'moderate', 'high', 'critical'])->default('low');
            $table->timestamp('screened_at')->useCurrent();
            $table->boolean('followup_required')->default(false);
            $table->text('followup_notes')->nullable();
            $table->foreignId('counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('wellbeing_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('mood_level'); // 1 = Very Bad, 5 = Excellent
            $table->text('journal_entry')->nullable();
            $table->json('activities')->nullable();
            $table->float('sleep_hours')->nullable();
            $table->boolean('is_private')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellbeing_journals');
        Schema::dropIfExists('wellbeing_screening_responses');
        Schema::dropIfExists('wellbeing_screenings');
    }
};
