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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'active', 'closed', 'archived'])->default('draft');
            $table->string('target_role')->nullable(); // null = all, or 'student', 'parent', 'guru', 'admin', etc.
            $table->foreignId('target_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->boolean('is_anonymous')->default(false);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('max_responses')->nullable();
            $table->boolean('send_notification')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'target_role']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->string('question_text');
            $table->enum('type', ['text', 'textarea', 'radio', 'checkbox', 'select', 'scale', 'date'])->default('text');
            $table->json('options')->nullable(); // For radio/checkbox/select
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0);
            $table->integer('scale_min')->nullable();
            $table->integer('scale_max')->nullable();
            $table->string('scale_min_label')->nullable();
            $table->string('scale_max_label')->nullable();
            $table->timestamps();

            $table->index(['survey_id', 'order']);
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('answers');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('survey_id');
            $table->unique(['survey_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
