<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_animation_preferences')) {
            Schema::create('user_animation_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('reduce_motion')->default(false);
                $table->enum('haptic_level', ['off', 'light', 'medium', 'strong'])->default('medium');
                $table->boolean('skeleton_loading')->default(true);
                $table->boolean('spring_animations')->default(true);
                $table->boolean('parallax_effects')->default(true);
                $table->enum('page_transitions', ['none', 'fade', 'slide', 'shared_element'])->default('slide');
                $table->enum('pull_refresh_style', ['classic', 'physics', 'lottie'])->default('physics');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_animation_preferences');
    }
};
