<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Universal Multi-Role Device Tokens
        if (! Schema::hasTable('user_device_tokens')) {
            Schema::create('user_device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('device_id')->index();
                $table->string('device_name')->nullable();
                $table->enum('platform', ['android', 'ios', 'tablet', 'web'])->default('android');
                $table->text('fcm_token');
                $table->text('apns_token')->nullable();
                $table->string('os_version')->nullable();
                $table->string('app_version')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('badge_count')->default(0);
                $table->timestamp('last_active_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'device_id']);
            });
        }

        // 2. Mobile App Remote Configuration & Version Gate
        if (! Schema::hasTable('mobile_app_configs')) {
            Schema::create('mobile_app_configs', function (Blueprint $table) {
                $table->id();
                $table->string('app_name')->default('SISTA Mobile');
                $table->integer('min_android_build')->default(1);
                $table->integer('latest_android_build')->default(1);
                $table->integer('min_ios_build')->default(1);
                $table->integer('latest_ios_build')->default(1);
                $table->boolean('force_update')->default(false);
                $table->boolean('maintenance_mode')->default(false);
                $table->text('maintenance_message')->nullable();
                $table->string('play_store_url')->nullable();
                $table->string('app_store_url')->nullable();
                $table->json('active_features')->nullable();
                $table->timestamps();
            });
        }

        // 3. Mobile Biometric Credentials (FaceID / Fingerprint)
        if (! Schema::hasTable('mobile_biometric_credentials')) {
            Schema::create('mobile_biometric_credentials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('device_id')->index();
                $table->text('public_key');
                $table->enum('biometric_type', ['fingerprint', 'face_id', 'iris'])->default('fingerprint');
                $table->boolean('is_enabled')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'device_id']);
            });
        }

        // 4. Mobile Sync Logs
        if (! Schema::hasTable('mobile_sync_logs')) {
            Schema::create('mobile_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('device_id')->index();
                $table->string('sync_type')->default('delta_pull');
                $table->integer('records_synced')->default(0);
                $table->timestamp('synced_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_sync_logs');
        Schema::dropIfExists('mobile_biometric_credentials');
        Schema::dropIfExists('mobile_app_configs');
        Schema::dropIfExists('user_device_tokens');
    }
};
