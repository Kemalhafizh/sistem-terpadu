<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 68.1: Advanced WCAG 2.2 AA Compliance Engine
        if (! Schema::hasTable('wcag_scan_reports')) {
            Schema::create('wcag_scan_reports', function (Blueprint $table) {
                $table->id();
                $table->string('scanned_url');
                $table->decimal('compliance_score', 5, 2)->default(100.00);
                $table->integer('total_issues')->default(0);
                $table->integer('critical_issues')->default(0);
                $table->timestamp('scanned_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wcag_issues')) {
            Schema::create('wcag_issues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wcag_scan_report_id')->constrained('wcag_scan_reports')->cascadeOnDelete();
                $table->string('violation_type'); // color_contrast, missing_alt, aria_label, focus_trap, heading_order
                $table->string('dom_element_selector')->nullable();
                $table->text('description');
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->boolean('is_resolved')->default(false);
                $table->timestamps();
            });
        }

        // 68.2: Dynamic Localization Strings Database Store
        if (! Schema::hasTable('localization_strings')) {
            Schema::create('localization_strings', function (Blueprint $table) {
                $table->id();
                $table->string('group_name'); // navigation, auth, system, dashboard
                $table->string('string_key');
                $table->text('text_id'); // Indonesian
                $table->text('text_en')->nullable(); // English
                $table->text('text_ar')->nullable(); // Arabic (RTL)
                $table->timestamps();

                $table->unique(['group_name', 'string_key']);
            });
        }

        // 68.3: Voice Navigation Commands Dictionary
        if (! Schema::hasTable('voice_navigation_commands')) {
            Schema::create('voice_navigation_commands', function (Blueprint $table) {
                $table->id();
                $table->string('language_code', 5)->default('id'); // id, en, ar
                $table->string('command_phrase'); // "buka rapor", "lihat jadwal", "bayar spp"
                $table->string('action_url'); // route or url
                $table->string('target_module')->default('dashboard');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 68.4: Theme & Universal Design Preferences
        if (! Schema::hasTable('theme_preferences')) {
            Schema::create('theme_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('color_mode', ['light', 'dark', 'system', 'high_contrast'])->default('system');
                $table->string('font_scaling')->default('100%'); // 90%, 100%, 115%, 130%
                $table->boolean('reduce_animations')->default(false);
                $table->boolean('dyslexic_font_enabled')->default(false);
                $table->string('primary_color_hex')->nullable();
                $table->timestamps();

                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_preferences');
        Schema::dropIfExists('voice_navigation_commands');
        Schema::dropIfExists('localization_strings');
        Schema::dropIfExists('wcag_issues');
        Schema::dropIfExists('wcag_scan_reports');
    }
};
