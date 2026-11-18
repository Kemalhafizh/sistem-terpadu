<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_device_tokens')) {
            Schema::create('user_device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('device_id', 128)->nullable()->index();
                $table->string('device_token', 512)->nullable();
                $table->string('device_type', 32)->default('android'); // android, ios, wearos
                $table->string('device_name', 128)->nullable();
                $table->string('platform', 32)->default('android');
                $table->text('fcm_token')->nullable();
                $table->text('apns_token')->nullable();
                $table->string('os_version', 64)->nullable();
                $table->string('app_version', 64)->nullable();
                $table->integer('badge_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_active_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('sync_logs')) {
            Schema::create('sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('device_id', 128);
                $table->string('sync_type', 32)->default('delta'); // delta, full, mutation
                $table->integer('records_synced')->default(0);
                $table->integer('conflicts_resolved')->default(0);
                $table->string('status', 32)->default('success');
                $table->json('payload_summary')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'device_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('user_device_tokens');
    }
};
