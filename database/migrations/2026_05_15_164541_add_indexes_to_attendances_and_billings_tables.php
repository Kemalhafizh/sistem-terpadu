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
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasIndex('attendances', 'attendances_date_index')) {
                $table->index('date');
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            if (! Schema::hasIndex('billings', 'billings_status_index')) {
                $table->index('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasIndex('attendances', 'attendances_date_index')) {
                $table->dropIndex(['date']);
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasIndex('billings', 'billings_status_index')) {
                $table->dropIndex(['status']);
            }
        });
    }
};
