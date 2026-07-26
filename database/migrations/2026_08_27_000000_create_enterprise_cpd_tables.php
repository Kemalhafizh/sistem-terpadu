<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpd_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->float('credit_weight')->default(1.0);
            $table->integer('max_credits_per_year')->nullable();
            $table->timestamps();
        });

        Schema::create('cpd_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cpd_category_id')->constrained('cpd_categories')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('provider')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('hours')->default(8);
            $table->float('credits_earned')->default(2.0);
            $table->string('certificate_path')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cpd_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('academic_year', 9);
            $table->float('target_credits')->default(12.0);
            $table->float('achieved_credits')->default(0);
            $table->enum('status', ['on_track', 'behind', 'exceeded'])->default('on_track');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_targets');
        Schema::dropIfExists('cpd_activities');
        Schema::dropIfExists('cpd_categories');
    }
};
