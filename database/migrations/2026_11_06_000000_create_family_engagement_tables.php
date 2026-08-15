<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->text('parent_guide_notes');
            $table->integer('estimated_parent_time_minutes')->default(15);
            $table->boolean('parent_reviewed')->default(false);
            $table->text('parent_feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('class_feed_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->json('target_audience');
            $table->text('content');
            $table->json('media_urls')->nullable();
            $table->enum('post_type', ['general_update', 'behavior_note', 'achievement_highlight'])->default('general_update');
            $table->boolean('requires_acknowledgment')->default(false);
            $table->timestamps();
        });

        Schema::create('family_learning_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('academic_term');
            $table->text('focus_area');
            $table->json('school_action_items');
            $table->json('home_action_items');
            $table->enum('progress_status', ['on_track', 'needs_attention', 'completed'])->default('on_track');
            $table->date('next_checkin_date');
            $table->timestamps();
        });

        Schema::create('parent_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('parent_name');
            $table->string('profession');
            $table->json('skill_tags');
            $table->boolean('available_for_guest_lecture')->default(true);
            $table->boolean('available_for_mentorship')->default(false);
            $table->text('past_contributions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_skills');
        Schema::dropIfExists('family_learning_plans');
        Schema::dropIfExists('class_feed_posts');
        Schema::dropIfExists('family_tasks');
    }
};
