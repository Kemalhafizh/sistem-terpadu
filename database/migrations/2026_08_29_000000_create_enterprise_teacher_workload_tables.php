<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_workloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('academic_year', 9);
            $table->string('semester')->default('ganjil');
            $table->integer('teaching_hours_per_week')->default(24);
            $table->json('additional_duties')->nullable();
            $table->json('extracurricular_duties')->nullable();
            $table->float('total_workload_score')->default(30.0);
            $table->enum('status', ['under', 'balanced', 'over'])->default('balanced');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_workloads');
    }
};
