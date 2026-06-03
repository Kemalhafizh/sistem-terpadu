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
        Schema::create('eds_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('eds_standards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SNP-1, SNP-2, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('eds_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_id')->constrained('eds_standards')->cascadeOnDelete();
            $table->string('code')->unique(); // 1.1, 1.2, 2.1, etc.
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('eds_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('eds_periods')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('eds_indicators')->cascadeOnDelete();
            $table->integer('score')->default(0); // 1-4
            $table->text('evidence')->nullable();
            $table->json('document_paths')->nullable(); // Array of uploaded document paths
            $table->text('recommendation')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['period_id', 'indicator_id'], 'eds_assessment_period_indicator_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eds_assessments');
        Schema::dropIfExists('eds_indicators');
        Schema::dropIfExists('eds_standards');
        Schema::dropIfExists('eds_periods');
    }
};
