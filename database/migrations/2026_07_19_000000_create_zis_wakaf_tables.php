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
        Schema::create('charity_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['zakat', 'infaq', 'shodaqoh', 'wakaf']);
            $table->text('description');
            $table->decimal('target_amount', 12, 2)->nullable();
            $table->decimal('collected_amount', 12, 2)->default(0.00);
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('charity_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_campaign_id')->constrained('charity_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // NULL if anonymous/non-logged
            $table->string('donor_name'); // Custom/fallback name if anonymous
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['transfer', 'wadiah_wallet', 'cash']);
            $table->enum('status', ['pending', 'verified', 'cancelled'])->default('pending');
            $table->string('evidence_path')->nullable(); // proof of bank transfer
            $table->boolean('is_anonymous')->default(false);
            $table->string('receipt_number')->unique(); // ZIS-YYYYMMDD-RAND
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charity_donations');
        Schema::dropIfExists('charity_campaigns');
    }
};
