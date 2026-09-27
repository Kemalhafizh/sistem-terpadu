<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_gamification')) {
            Schema::create('user_gamification', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('total_xp')->default(0);
                $table->integer('level')->default(1);
                $table->string('level_title')->default('Thalibul Ilmi Mubtadi');
                $table->integer('current_streak_days')->default(0);
                $table->integer('longest_streak_days')->default(0);
                $table->date('last_active_date')->nullable();
                $table->json('badges_earned')->nullable();
                $table->integer('monthly_rank')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('xp_transactions')) {
            Schema::create('xp_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('xp_amount');
                $table->enum('source', ['presensi', 'tugas', 'ujian', 'mutabaah', 'baca_buku', 'streak_bonus', 'achievement']);
                $table->string('description');
                $table->date('earned_date');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('achievement_badges')) {
            Schema::create('achievement_badges', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('description');
                $table->string('icon_emoji');
                $table->enum('category', ['streak', 'academic', 'ibadah', 'social']);
                $table->json('criteria');
                $table->integer('xp_reward');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_badges');
        Schema::dropIfExists('xp_transactions');
        Schema::dropIfExists('user_gamification');
    }
};
