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

        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasIndex('registrations', 'registrations_status_index')) {
                $table->index('status');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasIndex('payments', 'payments_payment_date_index')) {
                $table->index('payment_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payment_date']);
        });
    }
};
