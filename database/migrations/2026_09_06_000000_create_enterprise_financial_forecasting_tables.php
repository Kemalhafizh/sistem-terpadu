<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('scenario_name');
            $table->decimal('projected_revenue', 15, 2);
            $table->decimal('projected_expense', 15, 2);
            $table->decimal('confidence_interval', 5, 2)->default(95.00);
            $table->json('parameters')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_forecasts');
    }
};
