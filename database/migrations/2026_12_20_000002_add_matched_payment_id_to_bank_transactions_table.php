<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * TreasuryReconciliationService::autoReconcile used to write a fixed
 * 5 matched / 5 unmatched without looking at the statement. It now matches
 * each bank credit to a real Payment; `matched_payment_id` records which one,
 * so the same payment is never used to explain two bank transactions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->foreignId('matched_payment_id')->nullable()->after('matched_billing_id')
                ->constrained('payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('matched_payment_id');
        });
    }
};
