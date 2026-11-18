<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('event_category')->nullable()->default('all');
                $table->boolean('digest_mode')->default(false);
                $table->string('digest_interval')->default('realtime');
                $table->json('channel_preferences')->nullable();
                $table->json('quiet_hours')->nullable()->comment('{enabled: true, start: "22:00", end: "04:30"}');
                $table->boolean('smart_grouping')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('notification_preferences', function (Blueprint $table) {
                if (! Schema::hasColumn('notification_preferences', 'digest_mode')) {
                    $table->boolean('digest_mode')->default(false);
                }
                if (! Schema::hasColumn('notification_preferences', 'digest_interval')) {
                    $table->string('digest_interval')->default('realtime');
                }
                if (! Schema::hasColumn('notification_preferences', 'channel_preferences')) {
                    $table->json('channel_preferences')->nullable();
                }
                if (! Schema::hasColumn('notification_preferences', 'quiet_hours')) {
                    $table->json('quiet_hours')->nullable();
                }
                if (! Schema::hasColumn('notification_preferences', 'smart_grouping')) {
                    $table->boolean('smart_grouping')->default(true);
                }
                if (! Schema::hasColumn('notification_preferences', 'event_category')) {
                    $table->string('event_category')->nullable()->default('all');
                }
            });
        }
    }

    public function down(): void
    {
        // Keep table intact
    }
};
