<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * FinancialScoreboardService used to write fixed numbers here (cash reserve
 * 1.25 M, burn rate 185 jt, …) and invent 500/470 jt of billing when none
 * existed. Metrics are now computed from real records, and a metric with no
 * source is stored as NULL ("belum ada data") instead of a default 0.
 * `details` records where each number came from (period, record counts,
 * as-of dates) so the dashboard can say so.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_health_metrics', function (Blueprint $table) {
            $table->decimal('cash_reserve', 15, 2)->nullable()->default(null)->change();
            $table->decimal('monthly_burn_rate', 15, 2)->nullable()->default(null)->change();
            $table->decimal('projected_revenue', 15, 2)->nullable()->default(null)->change();
            $table->decimal('fee_collection_rate', 5, 2)->nullable()->default(null)->change();
            $table->json('details')->nullable()->after('expense_distribution');
        });
    }

    public function down(): void
    {
        Schema::table('financial_health_metrics', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }
};
