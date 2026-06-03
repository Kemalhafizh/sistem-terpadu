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
        // 1. Kategori Pembayaran (SPP, Uang Gedung, dll)
        Schema::create('payment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->boolean('is_monthly')->default(false);
            $table->timestamps();
        });

        // 2. Tagihan (Billing)
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_category_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->string('month')->nullable(); // Untuk SPP
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamps();
        });

        // 3. Pembayaran (Payment)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users'); // Staf TU
            $table->decimal('amount_paid', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash'); // cash, transfer
            $table->string('receipt_number')->unique();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('billings');
        Schema::dropIfExists('payment_categories');
    }
};
