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
        // 1. OSIS Members Table
        Schema::create('osis_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('position'); // e.g. "Ketua Umum", "Sekretaris", "Sekbid 1"
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. OSIS Work Programs Table
        Schema::create('osis_work_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('title'); // e.g. "Bakti Sosial Ramadhan 2026"
            $table->string('sekbid'); // e.g. "Sekbid 1: Keimanan & Ketaqwaan"
            $table->text('description');
            $table->decimal('budget_plan', 12, 2)->default(0);
            $table->decimal('budget_actual', 12, 2)->default(0);
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->text('report')->nullable(); // Laporan pertanggungjawaban LPJ
            $table->json('photo_paths')->nullable();
            $table->timestamps();
        });

        // 3. OSIS Elections Table
        Schema::create('osis_elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('title'); // e.g. "Pemilihan Ketua OSIS 2026/2027"
            $table->date('voting_start');
            $table->date('voting_end');
            $table->enum('status', ['upcoming', 'active', 'closed', 'announced'])->default('upcoming');
            $table->timestamps();
        });

        // 4. OSIS Candidates Table
        Schema::create('osis_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('osis_elections')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('candidate_number');
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('photo_path')->nullable();
            $table->integer('vote_count')->default(0);
            $table->timestamps();
        });

        // 5. OSIS Votes Table (Secure & Anonymous Ballot)
        Schema::create('osis_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('osis_elections')->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('osis_candidates')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['election_id', 'voter_id']); // 1 siswa 1 suara
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osis_votes');
        Schema::dropIfExists('osis_candidates');
        Schema::dropIfExists('osis_elections');
        Schema::dropIfExists('osis_work_programs');
        Schema::dropIfExists('osis_members');
    }
};
