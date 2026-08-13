<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('category', [
                'strategi_mengajar', 'penilaian_formatif', 'manajemen_kelas', 
                'teknologi_pembelajaran', 'pendidikan_inklusif', 'kurikulum_merdeka', 
                'keislaman_integrasi', 'penelitian_tindakan_kelas', 'lainnya'
            ]);
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_article_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('knowledge_article_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['article_id', 'user_id']);
        });

        Schema::create('lesson_studies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->foreignId('model_teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
            $table->enum('phase', ['plan', 'do', 'see', 'reflect', 'completed'])->default('plan');
            $table->date('session_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->text('plan_notes')->nullable();
            $table->text('observation_focus')->nullable();
            $table->text('reflection_summary')->nullable();
            $table->text('improvement_action')->nullable();
            $table->string('rpp_file_path')->nullable();
            $table->string('video_recording_path')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });

        Schema::create('lesson_study_observers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_study_id')->constrained('lesson_studies')->onDelete('cascade');
            $table->foreignId('observer_id')->constrained('users')->onDelete('cascade');
            $table->text('observation_notes')->nullable();
            $table->integer('student_engagement_score')->nullable();
            $table->integer('teaching_quality_score')->nullable();
            $table->integer('material_clarity_score')->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('suggestions')->nullable();
            $table->timestamps();
            $table->unique(['lesson_study_id', 'observer_id']);
        });

        Schema::create('plc_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['rumpun_mapel', 'lintas_mapel', 'jabatan', 'minat_khusus']);
            $table->foreignId('coordinator_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('plc_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plc_group_id')->constrained('plc_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['coordinator', 'member'])->default('member');
            $table->timestamps();
            $table->unique(['plc_group_id', 'user_id']);
        });

        Schema::create('plc_discussions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('plc_group_id')->constrained('plc_groups')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->integer('reply_count')->default(0);
            $table->timestamps();
        });

        Schema::create('plc_discussion_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained('plc_discussions')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_best_answer')->default(false);
            $table->timestamps();
        });

        Schema::create('shared_resources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('uploader_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('resource_type', [
                'rpp', 'bahan_ajar', 'bank_soal', 'media_pembelajaran', 
                'video_microteaching', 'modul', 'lembar_kerja', 'rubrik_penilaian', 'lainnya'
            ]);
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->enum('grade_level', ['x', 'xi', 'xii', 'all'])->default('all');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->integer('download_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->json('tags')->nullable();
            $table->enum('status', ['pending_review', 'approved', 'rejected'])->default('pending_review');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('shared_resource_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('shared_resources')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating');
            $table->text('review')->nullable();
            $table->timestamps();
            $table->unique(['resource_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shared_resource_ratings');
        Schema::dropIfExists('shared_resources');
        Schema::dropIfExists('plc_discussion_replies');
        Schema::dropIfExists('plc_discussions');
        Schema::dropIfExists('plc_group_members');
        Schema::dropIfExists('plc_groups');
        Schema::dropIfExists('lesson_study_observers');
        Schema::dropIfExists('lesson_studies');
        Schema::dropIfExists('knowledge_article_likes');
        Schema::dropIfExists('knowledge_article_comments');
        Schema::dropIfExists('knowledge_articles');
    }
};