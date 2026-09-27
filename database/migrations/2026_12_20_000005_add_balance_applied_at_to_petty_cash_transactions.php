<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Marks when an approved petty-cash transaction actually moved its fund's
 * balance. PettyCashService sets it on approval.
 *
 * Before PettyCashService, approving a transaction never changed the fund's
 * current_balance. Those rows stay NULL here — "approved, not yet applied" —
 * so `php artisan treasury:apply-legacy-petty-cash` can show and (after
 * review) apply exactly them, once, without double-counting anything.
 *
 * Deploy this together with PettyCashService: an approval made by the new
 * service without this column would be indistinguishable from a legacy one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->timestamp('balance_applied_at')->nullable()->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash_transactions', function (Blueprint $table) {
            $table->dropColumn('balance_applied_at');
        });
    }
};
