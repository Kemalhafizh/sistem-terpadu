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
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('body');
            $table->foreignId('user_id')->constrained('users');            // Pembuat thread
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('elearning_class_id')->nullable()->constrained('elearning_classes')->nullOnDelete();
            $table->enum('scope', ['class', 'subject', 'school'])->default('class');  // Cakupan visibilitas
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);        // Tidak bisa dijawab lagi
            $table->boolean('is_announcement')->default(false);  // Highlight sebagai pengumuman
            $table->string('attachment_path')->nullable();
            $table->integer('reply_count')->default(0);          // Denormalized counter
            $table->timestamp('last_reply_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['classroom_id', 'is_pinned', 'last_reply_at']);
            $table->index(['subject_id', 'created_at']);
            $table->index('scope');
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('parent_id')->nullable()->constrained('forum_replies')->nullOnDelete(); // Nested reply
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->boolean('is_best_answer')->default(false);   // Guru menandai jawaban terbaik
            $table->softDeletes();
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
            $table->index('parent_id');
        });

        Schema::create('forum_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->morphs('reactable');                          // Bisa untuk thread atau reply
            $table->string('type')->default('like');              // 'like', 'helpful', 'insightful'
            $table->timestamps();

            $table->unique(['user_id', 'reactable_id', 'reactable_type', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_reactions');
        Schema::dropIfExists('forum_replies');
        Schema::dropIfExists('forum_threads');
    }
};
