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
        Schema::create('uat_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('page_url');
            $table->tinyInteger('rating'); // 1-5
            $table->enum('category', ['bug', 'ui_suggestion', 'unclear_feature', 'slow', 'other']);
            $table->text('description');
            $table->string('screenshot_path')->nullable();
            $table->json('device_info')->nullable(); // browser, os, screen size, role, etc.
            $table->enum('status', ['open', 'in_progress', 'fixed', 'verified', 'wont_fix'])->default('open');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->text('developer_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uat_feedback');
    }
};
