<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahsin_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('employees')->onDelete('cascade');
            $table->string('surah_name');
            $table->integer('start_ayah');
            $table->integer('end_ayah');
            $table->json('tajwid_errors')->nullable();
            $table->json('maqam_recognized')->nullable();
            $table->integer('fluency_score');
            $table->integer('makhraj_score');
            $table->text('teacher_notes')->nullable();
            $table->date('assessment_date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('islamic_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->enum('event_type', ['ramadan', 'muharram', 'eid', 'custom', 'prayer_schedule_change']);
            $table->date('gregorian_start_date');
            $table->date('gregorian_end_date')->nullable();
            $table->string('hijri_start_date');
            $table->string('hijri_end_date')->nullable();
            $table->text('description');
            $table->boolean('is_school_holiday')->default(false);
            $table->json('affected_academic_schedules')->nullable();
            $table->timestamps();
        });

        Schema::create('spiritual_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->date('record_date');
            $table->json('daily_prayers');
            $table->json('fasting_record')->nullable();
            $table->integer('akhlaq_peer_score')->nullable();
            $table->integer('akhlaq_teacher_score')->nullable();
            $table->text('character_building_notes')->nullable();
            $table->foreignId('evaluator_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('dawah_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('employees')->onDelete('cascade');
            $table->string('title');
            $table->enum('content_type', ['khutbah', 'article', 'hadith_daily', 'study_material']);
            $table->longText('content_body');
            $table->json('references')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dawah_contents');
        Schema::dropIfExists('spiritual_portfolios');
        Schema::dropIfExists('islamic_events');
        Schema::dropIfExists('tahsin_records');
    }
};