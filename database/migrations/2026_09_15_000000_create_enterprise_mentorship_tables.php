<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('users')->cascadeOnDelete();
            $table->json('expertise_areas')->nullable();
            $table->integer('max_mentees')->default(3);
            $table->boolean('is_available')->default(true);
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('mentorship_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_profile_id')->constrained('mentor_profiles')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->text('goals')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_pairs');
        Schema::dropIfExists('mentor_profiles');
    }
};
