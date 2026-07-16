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
        // 1. Achievements Table
        Schema::create('extracurricular_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->string('title'); // e.g. "Juara 1 Lomba Debat Bahasa Inggris"
            $table->enum('level', ['sekolah', 'kecamatan', 'kota', 'provinsi', 'nasional', 'internasional']);
            $table->date('event_date');
            $table->string('organizer');
            $table->json('participants')->nullable(); // [student_id, student_id, ...]
            $table->string('certificate_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Grades Table
        Schema::create('extracurricular_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_member_id')->constrained('extracurricular_members')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->enum('grade', ['A', 'B', 'C', 'D'])->nullable(); // A-D for rapor
            $table->text('description')->nullable(); // deskripsi capaian
            $table->timestamps();

            $table->unique(['extracurricular_member_id', 'academic_year_id'], 'member_academic_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_grades');
        Schema::dropIfExists('extracurricular_achievements');
    }
};
