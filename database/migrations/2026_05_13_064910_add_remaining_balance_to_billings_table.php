<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->decimal('remaining_balance', 15, 2)->after('amount')->default(0);
        });

        // Sync existing data: remaining_balance = amount (assuming no partial payments yet for simplicity)
        DB::table('billings')->update(['remaining_balance' => DB::raw('amount')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn('remaining_balance');
        });
    }
};
