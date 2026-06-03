<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add gamification columns to students table
        Schema::table('students', function (Blueprint $table) {
            $table->integer('total_xp')->default(0)->after('address');
            $table->integer('current_streak')->default(0)->after('total_xp');
            $table->integer('longest_streak')->default(0)->after('current_streak');
        });

        // 2. Create achievement_definitions table
        Schema::create('achievement_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // 'perfect_attendance_month', 'tahfidz_juz'
            $table->string('name');
            $table->text('description');
            $table->string('icon');           // Emoji or icon name (e.g. '🔥', '📚')
            $table->string('color')->default('#f59e0b');
            $table->string('category')->default('akademik'); // 'akademik', 'kehadiran', 'sosial', 'keislaman', 'khusus'
            $table->string('rarity')->default('common');     // 'common', 'rare', 'epic', 'legendary'
            $table->integer('xp_reward')->default(10);
            $table->json('criteria')->nullable();            // {"type":"attendance_streak","value":30}
            $table->boolean('is_auto')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Create student_achievements table
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained('achievement_definitions')->cascadeOnDelete();
            $table->date('earned_at');
            $table->text('context')->nullable(); // "Hadir 30 hari berturut-turut di Mei 2026"
            $table->timestamps();

            $table->unique(['student_id', 'achievement_id', 'earned_at']);
        });

        // 4. Create student_xp_logs table
        Schema::create('student_xp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('xp_amount');
            $table->string('source');            // 'attendance', 'assignment', 'forum', 'achievement', 'tahfidz', 'mutabaah'
            $table->string('description');
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_xp_logs');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('achievement_definitions');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['total_xp', 'current_streak', 'longest_streak']);
        });
    }
};
