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
        // 1. Performance Evaluations Table
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->string('period'); // e.g. "Semester Genap 2025/2026"
            $table->json('criteria_scores'); // {"pedagogik": 85, "profesional": 90, "sosial": 80, "kepribadian": 88}
            $table->decimal('total_score', 5, 2);
            $table->enum('grade', ['A', 'B', 'C', 'D', 'E']);
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('goals')->nullable();
            $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
            $table->timestamps();
        });

        // 2. Employee Trainings Table
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('title'); // e.g. "Workshop Kurikulum Merdeka"
            $table->enum('type', ['workshop', 'seminar', 'sertifikasi', 'diklat', 'webinar', 'lainnya']);
            $table->string('organizer'); // e.g. "Kemendikbud" / "YBWSA"
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('hours')->nullable(); // Durasi JP
            $table->string('certificate_path')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Employee Documents Table
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('title'); // e.g. "SK Pengangkatan 2026"
            $table->enum('type', ['sk', 'kontrak', 'sertifikasi', 'ijazah', 'ktp', 'npwp', 'bpjs', 'lainnya']);
            $table->string('file_path');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_trainings');
        Schema::dropIfExists('performance_evaluations');
    }
};
