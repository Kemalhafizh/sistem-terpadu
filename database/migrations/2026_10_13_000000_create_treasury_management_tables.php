<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_name');
            $table->enum('account_type', ['giro', 'tabungan', 'virtual_account']);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('statement_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('file_path');
            $table->integer('total_transactions')->default(0);
            $table->integer('matched_count')->default(0);
            $table->integer('unmatched_count')->default(0);
            $table->enum('status', ['imported', 'processing', 'reconciled', 'has_discrepancies'])->default('imported');
            $table->foreignId('imported_by')->constrained('users');
            $table->timestamps();
        });
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('match_status', ['unmatched', 'auto_matched', 'manual_matched', 'disputed'])->default('unmatched');
            $table->unsignedBigInteger('matched_billing_id')->nullable();
            $table->foreignId('matched_by')->nullable()->constrained('users');
            $table->text('match_notes')->nullable();
            $table->timestamps();
        });
        Schema::create('petty_cash_funds', function (Blueprint $table) {
            $table->id();
            $table->string('fund_name');
            $table->foreignId('custodian_id')->constrained('users');
            $table->decimal('imprest_amount', 15, 2);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('petty_cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->constrained('petty_cash_funds')->cascadeOnDelete();
            $table->enum('type', ['pengeluaran', 'pengisian_ulang']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->date('transaction_date');
            $table->enum('category', ['atk', 'konsumsi', 'transportasi', 'fotocopy', 'peralatan_kecil', 'kebersihan', 'obat_p3k', 'lainnya']);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('daily_cash_positions', function (Blueprint $table) {
            $table->id();
            $table->date('position_date');
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('total_inflow', 15, 2)->default(0);
            $table->decimal('total_outflow', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2);
            $table->timestamps();
            $table->unique(['position_date', 'bank_account_id']);
        });
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('invoice_number')->nullable();
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('payment_method', ['transfer', 'cash', 'giro', 'auto_debit']);
            $table->enum('status', ['scheduled', 'paid', 'overdue', 'cancelled'])->default('scheduled');
            $table->foreignId('bank_account_id')->nullable()->constrained();
            $table->string('proof_file_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('daily_cash_positions');
        Schema::dropIfExists('petty_cash_transactions');
        Schema::dropIfExists('petty_cash_funds');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_accounts');
    }
};