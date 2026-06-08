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
        Schema::create('daily_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['ulangan_harian', 'kuis', 'praktikum', 'project', 'presentasi', 'tugas']);
            $table->date('assessment_date');
            $table->decimal('max_score', 5, 2)->default(100);
            $table->decimal('kkm', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_assessment_id')->constrained('daily_assessments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('needs_remedial')->default(false);
            $table->decimal('remedial_score', 5, 2)->nullable();
            $table->enum('remedial_status', ['not_needed', 'pending', 'completed'])->default('not_needed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['daily_assessment_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_assessment_scores');
        Schema::dropIfExists('daily_assessments');
    }
};
