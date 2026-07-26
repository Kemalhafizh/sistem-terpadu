<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_pathways', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['IPA', 'IPS', 'Bahasa', 'Keagamaan']);
            $table->string('description');
            $table->json('required_subjects')->nullable();
            $table->integer('required_credits')->default(120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_pathway_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_pathway_id')->constrained('academic_pathways')->cascadeOnDelete();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->enum('status', ['active', 'completed', 'changed'])->default('active');
            $table->timestamps();
        });

        Schema::create('graduation_requirements', function (Blueprint $table) {
            $table->id();
            $table->enum('requirement_type', ['subject_credit', 'attendance_minimum', 'tahfidz_minimum', 'community_service', 'thesis', 'exam_pass']);
            $table->string('description');
            $table->string('threshold_value');
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });

        Schema::create('student_graduation_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('graduation_requirement_id')->constrained('graduation_requirements')->cascadeOnDelete();
            $table->string('current_value');
            $table->boolean('is_fulfilled')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_graduation_progress');
        Schema::dropIfExists('graduation_requirements');
        Schema::dropIfExists('student_pathway_enrollments');
        Schema::dropIfExists('academic_pathways');
    }
};
