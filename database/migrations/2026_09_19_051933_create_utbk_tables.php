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
        Schema::create('ptn_programs', function (Blueprint $table) {
            $table->id();
            $table->string('university_name');
            $table->string('program_name');
            $table->string('cluster'); // Soshum / Saintek
            $table->decimal('passing_grade', 8, 2); // Skor minimum yang dibutuhkan
            $table->integer('capacity')->default(50);
            $table->timestamps();
        });

        Schema::create('tryouts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->default(120);
            $table->enum('status', ['upcoming', 'active', 'closed'])->default('upcoming');
            $table->timestamps();
        });

        Schema::create('tryout_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_id')->constrained('tryouts')->cascadeOnDelete();
            $table->string('sub_test'); // TPS, Literasi Bahasa, Penalaran Matematika
            $table->text('question_text');
            $table->json('options'); // array of choices A, B, C, D, E
            $table->string('correct_answer');
            $table->decimal('difficulty_b', 8, 4)->default(0); // Item difficulty for IRT
            $table->decimal('discrimination_a', 8, 4)->default(1); // Item discrimination
            $table->timestamps();
        });

        Schema::create('tryout_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_id')->constrained('tryouts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('score_tps', 8, 2)->default(0);
            $table->decimal('score_literasi', 8, 2)->default(0);
            $table->decimal('score_penalaran', 8, 2)->default(0);
            $table->decimal('total_score', 8, 2)->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('answers_json')->nullable(); // Track student's answers
            $table->timestamps();

            $table->unique(['tryout_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tryout_attempts');
        Schema::dropIfExists('tryout_questions');
        Schema::dropIfExists('tryouts');
        Schema::dropIfExists('ptn_programs');
    }
};
