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
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content');
            $table->string('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('order')->default(0);
            $table->boolean('show_in_nav')->default(true);
            $table->timestamps();
        });

        Schema::create('cms_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('cover_image')->nullable();
            $table->enum('category', ['berita', 'prestasi', 'kegiatan', 'artikel'])->default('berita');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->integer('view_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
            $table->index('category');
        });

        Schema::create('cms_galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_path');
            $table->string('category')->nullable();
            $table->text('caption')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cms_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->text('content');
            $table->string('avatar_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_testimonials');
        Schema::dropIfExists('cms_galleries');
        Schema::dropIfExists('cms_posts');
        Schema::dropIfExists('cms_pages');
    }
};
