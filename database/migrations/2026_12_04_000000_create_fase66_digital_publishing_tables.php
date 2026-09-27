<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 66.1: School Digital Magazine & Newsletter
        if (! Schema::hasTable('digital_magazines')) {
            Schema::create('digital_magazines', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->integer('issue_number')->default(1);
                $table->longText('content_blocks')->nullable(); // JSON Blocks from Block Editor
                $table->string('cover_image_path')->nullable();
                $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('magazine_subscribers')) {
            Schema::create('magazine_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamps();
            });
        }

        // 66.2: Video Learning Studio & Streaming Platform
        if (! Schema::hasTable('video_studios')) {
            Schema::create('video_studios', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('original_file_path');
                $table->json('hls_playlist_paths')->nullable(); // resolutions: 360p, 720p, 1080p
                $table->string('thumbnail_path')->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->enum('transcoding_status', ['pending', 'processing', 'completed', 'failed'])->default('completed');
                $table->integer('view_count')->default(0);
                $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 66.3: Podcast & Audio Content Distribution
        if (! Schema::hasTable('podcasts')) {
            Schema::create('podcasts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('cover_image_path')->nullable();
                $table->string('category')->default('islamic_studies'); // islamic_studies, science, motivation, language
                $table->foreignId('host_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('podcast_episodes')) {
            Schema::create('podcast_episodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('podcast_id')->constrained('podcasts')->cascadeOnDelete();
                $table->string('title');
                $table->string('audio_file_path');
                $table->integer('duration_seconds')->default(0);
                $table->text('show_notes')->nullable();
                $table->integer('listen_count')->default(0);
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        // 66.4: Interactive Infographic & Data Story Builder
        if (! Schema::hasTable('data_stories')) {
            Schema::create('data_stories', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('summary')->nullable();
                $table->json('story_configuration'); // sections, scrollytelling steps, embedded charts
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->boolean('is_published')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_stories');
        Schema::dropIfExists('podcast_episodes');
        Schema::dropIfExists('podcasts');
        Schema::dropIfExists('video_studios');
        Schema::dropIfExists('magazine_subscribers');
        Schema::dropIfExists('digital_magazines');
    }
};
