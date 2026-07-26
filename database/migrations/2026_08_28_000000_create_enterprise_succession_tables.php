<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leadership_positions', function (Blueprint $table) {
            $table->id();
            $table->enum('title', ['kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'koordinator_bk', 'koordinator_tahfidz']);
            $table->foreignId('current_holder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('succession_priority', ['critical', 'high', 'medium'])->default('high');
            $table->date('review_date')->nullable();
            $table->timestamps();
        });

        Schema::create('succession_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leadership_position_id')->constrained('leadership_positions')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->enum('readiness_level', ['ready_now', 'ready_1yr', 'ready_3yr', 'developing'])->default('ready_1yr');
            $table->json('strengths')->nullable();
            $table->json('development_areas')->nullable();
            $table->text('development_plan')->nullable();
            $table->foreignId('endorsed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_candidates');
        Schema::dropIfExists('leadership_positions');
    }
};
