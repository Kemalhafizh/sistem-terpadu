<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 77.7.1 — "starts in 5 minutes" / "ends in 5 minutes" pushes to the
 * teacher. The timestamps make each reminder go out exactly once even though
 * the scheduler checks every minute (claimed with a conditional UPDATE).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->timestamp('start_reminder_sent_at')->nullable()->after('is_auto_closed');
            $table->timestamp('end_reminder_sent_at')->nullable()->after('start_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn(['start_reminder_sent_at', 'end_reminder_sent_at']);
        });
    }
};
