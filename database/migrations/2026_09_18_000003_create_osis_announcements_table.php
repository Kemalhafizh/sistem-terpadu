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
        Schema::create('osis_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->default('Pengurus OSIS');
            $table->string('category')->default('Umum');
            $table->text('content');
            $table->integer('likes_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->dateTime('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osis_announcements');
    }
};
