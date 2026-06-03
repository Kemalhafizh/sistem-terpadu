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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasIndex('users', 'users_role_index')) {
                $table->index('role');
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            if (! Schema::hasIndex('billings', 'billings_status_index')) {
                $table->index('status');
            }
            if (! Schema::hasIndex('billings', 'billings_student_id_index')) {
                $table->index('student_id');
            }
        });

        Schema::table('tahfidz_logs', function (Blueprint $table) {
            if (! Schema::hasIndex('tahfidz_logs', 'tahfidz_logs_student_id_index')) {
                $table->index('student_id');
            }
            if (! Schema::hasIndex('tahfidz_logs', 'tahfidz_logs_date_index')) {
                $table->index('date');
            }
        });

        Schema::table('mutabaah_logs', function (Blueprint $table) {
            if (! Schema::hasIndex('mutabaah_logs', 'mutabaah_logs_student_id_index')) {
                $table->index('student_id');
            }
            if (! Schema::hasIndex('mutabaah_logs', 'mutabaah_logs_date_index')) {
                $table->index('date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutabaah_logs', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['date']);
        });

        Schema::table('tahfidz_logs', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['date']);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
