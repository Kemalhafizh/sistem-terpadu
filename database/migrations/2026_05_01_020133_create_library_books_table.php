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
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('author')->nullable();
            $table->foreignId('category_id')->constrained('library_categories');
            $table->foreignId('subject_id')->nullable()->constrained('subjects');
            $table->enum('type', ['digital', 'physical'])->default('digital');
            $table->string('file_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_physical_available')->default(true);
            $table->integer('download_count')->default(0);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
