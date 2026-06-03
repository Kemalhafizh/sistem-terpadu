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
        Schema::create('tracer_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_data_id')->constrained('alumni_data')->cascadeOnDelete();
            $table->string('job_title')->nullable();
            $table->enum('salary_range', ['< 3M', '3M - 5M', '5M - 10M', '> 10M'])->nullable();
            $table->string('study_major')->nullable();
            $table->integer('relevance_score')->default(5); // Skala 1-5
            $table->integer('satisfaction_score')->default(5); // Skala 1-5
            $table->text('suggestions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracer_responses');
    }
};
