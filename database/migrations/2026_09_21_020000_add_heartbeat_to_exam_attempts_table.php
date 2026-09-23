<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FASE 72.2: CBT Live Proctoring — tracks whether the exam app is still
     * alive/foregrounded so the proctor dashboard can flag a student who
     * silently dropped connection (app killed, network loss) without
     * needing an explicit violation event.
     */
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_attempts', 'last_heartbeat_at')) {
                $table->timestamp('last_heartbeat_at')->nullable()->after('force_close_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('exam_attempts', 'last_heartbeat_at')) {
                $table->dropColumn('last_heartbeat_at');
            }
        });
    }
};
