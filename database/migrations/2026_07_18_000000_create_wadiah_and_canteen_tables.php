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
        Schema::create('wadiah_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('account_number')->unique();
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->decimal('daily_spending_limit', 12, 2)->default(50000.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('wadiah_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wadiah_account_id')->constrained('wadiah_accounts')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'purchase', 'refund']);
            $table->decimal('amount', 12, 2);
            $table->decimal('previous_balance', 12, 2);
            $table->decimal('current_balance', 12, 2);
            $table->string('reference_number')->unique();
            $table->string('description')->nullable();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('canteen_merchants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('canteen_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canteen_merchant_id')->constrained('canteen_merchants')->cascadeOnDelete();
            $table->foreignId('wadiah_transaction_id')->nullable()->constrained('wadiah_transactions')->nullOnDelete();
            $table->json('items_ordered');
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canteen_transactions');
        Schema::dropIfExists('canteen_merchants');
        Schema::dropIfExists('wadiah_transactions');
        Schema::dropIfExists('wadiah_accounts');
    }
};
