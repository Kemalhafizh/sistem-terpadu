<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ml_performance_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->decimal('predicted_gpa', 4, 2);
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->json('factors')->nullable();
            $table->date('prediction_date');
            $table->timestamps();
        });

        Schema::create('ml_intervention_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->constrained('ml_performance_predictions')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->text('action_taken');
            $table->string('status')->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_intervention_logs');
        Schema::dropIfExists('ml_performance_predictions');
    }
};
