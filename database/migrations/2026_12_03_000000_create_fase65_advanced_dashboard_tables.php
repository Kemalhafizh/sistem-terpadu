<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 65.4: Financial Health Scoreboard Metrics
        if (! Schema::hasTable('financial_health_metrics')) {
            Schema::create('financial_health_metrics', function (Blueprint $table) {
                $table->id();
                $table->date('report_date');
                $table->decimal('cash_reserve', 15, 2)->default(0);
                $table->decimal('monthly_burn_rate', 15, 2)->default(0);
                $table->decimal('projected_revenue', 15, 2)->default(0);
                $table->decimal('fee_collection_rate', 5, 2)->default(0); // in percentage
                $table->json('expense_distribution')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_health_metrics');
    }
};
