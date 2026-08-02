<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->string('vendor_name');
            $table->decimal('contract_value', 15, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expiring_soon', 'expired', 'terminated'])->default('active');
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('legal_contracts')->cascadeOnDelete();
            $table->date('renewal_date');
            $table->decimal('new_value', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_renewals');
        Schema::dropIfExists('legal_contracts');
    }
};
