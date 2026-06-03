<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Table for Portfolio Items (Achievements, Certificates, Projects, etc.)
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['prestasi', 'sertifikat', 'karya', 'proyek', 'organisasi', 'other'])->default('other');
            $table->date('date')->nullable();
            $table->string('file_path')->nullable();           // File upload for certificate or evidence
            $table->string('external_link')->nullable();       // External link (e.g. online projects)
            $table->boolean('is_verified')->default(false);    // Verification status from teacher/BK
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_public')->default(false);      // Visible on the public shareable portfolio link
            $table->timestamps();

            // Index for high-performance retrieval
            $table->index(['student_id', 'category', 'is_verified', 'is_public'], 'idx_portfolio_query');
        });

        // 2. Table for Award Badges (Master Badge Templates)
        Schema::create('portfolio_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');                            // "Juara Kelas", "Duta Seni", "Atlet Utama"
            $table->string('icon');                            // Emoji or icon class (e.g. "🏆", "🌟")
            $table->text('criteria')->nullable();              // Requirements to earn the badge
            $table->string('color')->default('#3b82f6');       // Aksen theme hex color
            $table->timestamps();
        });

        // 3. Table for Student Badges (Pivot Table)
        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('portfolio_badges')->cascadeOnDelete();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('earned_at');
            $table->text('notes')->nullable();                 // Reason/remark for awarding the badge
            $table->timestamps();

            $table->unique(['student_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_badges');
        Schema::dropIfExists('portfolio_badges');
        Schema::dropIfExists('portfolio_items');
    }
};
