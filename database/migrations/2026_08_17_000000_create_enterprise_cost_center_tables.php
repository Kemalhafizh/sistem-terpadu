<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('department', ['kurikulum', 'kesiswaan', 'humas', 'sarpras', 'tu', 'keuangan', 'keislaman']);
            $table->foreignId('head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('annual_budget_allocation', 15, 2);
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('cost_center_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_center_id')->constrained('cost_centers')->cascadeOnDelete();
            $table->enum('transaction_type', ['allocation', 'expense', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_center_transactions');
        Schema::dropIfExists('cost_centers');
    }
};
