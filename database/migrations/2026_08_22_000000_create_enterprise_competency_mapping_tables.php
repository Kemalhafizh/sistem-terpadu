<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('phase', ['E', 'F'])->default('E');
            $table->string('element');
            $table->string('cp_code')->unique();
            $table->text('cp_description');
            $table->timestamps();
        });

        Schema::create('learning_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_outcome_id')->constrained('learning_outcomes')->cascadeOnDelete();
            $table->string('tp_code');
            $table->text('tp_description');
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->integer('sequence_order')->default(1);
            $table->enum('assessment_type', ['formatif', 'sumatif'])->default('formatif');
            $table->enum('bloom_taxonomy_level', ['remember', 'understand', 'apply', 'analyze', 'evaluate', 'create'])->default('apply');
            $table->timestamps();
        });

        Schema::create('atp_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
            $table->integer('week_number');
            $table->integer('teaching_hours')->default(2);
            $table->text('learning_activities');
            $table->text('assessment_method');
            $table->text('resources')->nullable();
            $table->timestamps();
        });

        Schema::create('competency_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
            $table->string('academic_year', 9);
            $table->enum('status', ['not_started', 'in_progress', 'achieved', 'exceeded'])->default('in_progress');
            $table->text('evidence_notes')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_achievements');
        Schema::dropIfExists('atp_mappings');
        Schema::dropIfExists('learning_objectives');
        Schema::dropIfExists('learning_outcomes');
    }
};
