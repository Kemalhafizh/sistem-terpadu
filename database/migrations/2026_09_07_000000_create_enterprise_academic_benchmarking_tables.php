<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('exam_type')->default('ujian_nasional');
            $table->decimal('school_average', 5, 2);
            $table->decimal('national_average', 5, 2)->nullable();
            $table->integer('percentile_rank')->nullable();
            $table->year('year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_benchmarks');
    }
};
