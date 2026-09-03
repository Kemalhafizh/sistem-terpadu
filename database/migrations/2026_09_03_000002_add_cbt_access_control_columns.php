<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FASE 87: Token-Gated Mobile-Only CBT Access Control
     *
     * Adds token-based exam entry, mobile-only enforcement,
     * and force-close tracking columns.
     */
    public function up(): void
    {
        // === EXAMS TABLE: Token & Mobile-Only ===
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'access_token')) {
                $table->string('access_token', 6)->nullable()->after('status');
            }
            if (!Schema::hasColumn('exams', 'mobile_only')) {
                $table->boolean('mobile_only')->default(true)->after('access_token');
            }
            if (!Schema::hasColumn('exams', 'operator_id')) {
                $table->foreignId('operator_id')->nullable()->after('mobile_only')
                      ->constrained('users')->nullOnDelete();
            }
        });

        // === EXAM_ATTEMPTS TABLE: Platform tracking & Force-close ===
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_attempts', 'access_platform')) {
                $table->string('access_platform', 10)->nullable()->after('is_auto_submitted');
            }
            if (!Schema::hasColumn('exam_attempts', 'used_token')) {
                $table->string('used_token', 6)->nullable()->after('access_platform');
            }
            if (!Schema::hasColumn('exam_attempts', 'token_validated_at')) {
                $table->timestamp('token_validated_at')->nullable()->after('used_token');
            }
            if (!Schema::hasColumn('exam_attempts', 'force_closed')) {
                $table->boolean('force_closed')->default(false)->after('token_validated_at');
            }
            if (!Schema::hasColumn('exam_attempts', 'force_close_reason')) {
                $table->string('force_close_reason')->nullable()->after('force_closed');
            }
        });

        // Expand status enum to include 'force_closed' on MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE exam_attempts MODIFY COLUMN status ENUM('in_progress', 'submitted', 'graded', 'blocked_violation', 'timeout', 'force_closed') DEFAULT 'in_progress'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'operator_id')) {
                $table->dropForeign(['operator_id']);
                $table->dropColumn('operator_id');
            }
            if (Schema::hasColumn('exams', 'mobile_only')) {
                $table->dropColumn('mobile_only');
            }
            if (Schema::hasColumn('exams', 'access_token')) {
                $table->dropColumn('access_token');
            }
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $cols = array_filter(['access_platform', 'used_token', 'token_validated_at', 'force_closed', 'force_close_reason'], function ($c) {
                return Schema::hasColumn('exam_attempts', $c);
            });
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE exam_attempts MODIFY COLUMN status ENUM('in_progress', 'submitted', 'graded', 'blocked_violation', 'timeout') DEFAULT 'in_progress'");
        }
    }
};
