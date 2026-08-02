<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_bills', function (Blueprint $table) {
            $table->id();
            $table->enum('utility_type', ['electricity', 'water', 'internet', 'gas', 'waste'])->default('electricity');
            $table->string('billing_period');
            $table->decimal('amount', 15, 2);
            $table->decimal('usage_kwh_m3', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('carbon_emissions', function (Blueprint $table) {
            $table->id();
            $table->string('period');
            $table->decimal('co2_kg', 12, 2);
            $table->string('source_category');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_emissions');
        Schema::dropIfExists('utility_bills');
    }
};
