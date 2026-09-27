<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('design_tokens')) {
            Schema::create('design_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token_key')->unique()->comment('Contoh: color.primary, spacing.md, radius.card');
                $table->enum('category', ['color', 'spacing', 'radius', 'shadow', 'typography']);
                $table->json('value')->comment('Menyimpan varian light/dark/amoled');
                $table->text('description')->nullable();
                $table->boolean('is_customizable')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('theme_presets')) {
            Schema::create('theme_presets', function (Blueprint $table) {
                $table->id();
                $table->string('name')->comment('Sultan Agung Green, Ramadhan Gold, Exam Mode');
                $table->string('slug')->unique();
                $table->json('token_overrides')->nullable();
                $table->enum('trigger_type', ['time_of_day', 'event', 'season', 'manual'])->nullable();
                $table->json('trigger_config')->nullable()->comment('Misal: {start_hour:18, end_hour:6}');
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_theme_preferences')) {
            Schema::create('user_theme_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('active_preset')->nullable();
                $table->json('custom_overrides')->nullable();
                $table->boolean('auto_theme')->default(true);
                $table->boolean('dynamic_color')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_theme_preferences');
        Schema::dropIfExists('theme_presets');
        Schema::dropIfExists('design_tokens');
    }
};
