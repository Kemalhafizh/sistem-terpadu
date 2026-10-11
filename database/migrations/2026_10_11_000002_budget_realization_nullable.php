<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * A budget line's realisation comes from the ledger when its account code is
 * in the chart of accounts; otherwise TU enters it. Until then it is unknown
 * (null), not Rp 0 — a 0 read as "nothing spent" and "0% terserap".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_line_items', function (Blueprint $table) {
            $table->decimal('actual_amount', 15, 2)->nullable()->default(null)->change();
            $table->decimal('variance_amount', 15, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('budget_line_items', function (Blueprint $table) {
            $table->decimal('actual_amount', 15, 2)->default(0)->change();
            $table->decimal('variance_amount', 15, 2)->default(0)->change();
        });
    }
};
