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
        Schema::create('elearning_classes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('elearning_materials', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('elearning_class_id')->constrained('elearning_classes')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // PDF materi
            $table->string('video_url')->nullable(); // Youtube/Link
            $table->timestamps();
        });

        Schema::create('elearning_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('elearning_class_id')->constrained('elearning_classes')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->dateTime('due_at');
            $table->boolean('allow_late')->default(true);
            $table->boolean('is_secure_mode')->default(false); // Mobile secure lock-down mode
            $table->integer('max_violations')->default(3); // Toleransi pindah tab/app
            $table->timestamps();
        });

        Schema::create('elearning_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elearning_assignment_id')->constrained('elearning_assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->dateTime('submitted_at');
            $table->string('file_path')->nullable(); // Foto lembar jawaban atau PDF
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['submitted', 'graded', 'locked_cheated'])->default('submitted');
            $table->integer('violation_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elearning_submissions');
        Schema::dropIfExists('elearning_assignments');
        Schema::dropIfExists('elearning_materials');
        Schema::dropIfExists('elearning_classes');
    }
};
