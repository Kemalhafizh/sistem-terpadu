<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_dimensions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['p5', 'islamic'])->default('p5');
            $table->text('description')->nullable();
            $table->string('icon_name')->default('star');
            $table->integer('display_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('character_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('character_dimension_id')->constrained('character_dimensions')->cascadeOnDelete();
            $table->string('academic_year', 9);
            $table->enum('semester', ['odd', 'even'])->default('odd');
            $table->tinyInteger('score'); // 1 = Perlu Bimbingan, 2 = Cukup, 3 = Baik, 4 = Sangat Baik
            $table->text('evidence_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('merit_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('awarded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('character_dimension_id')->nullable()->constrained('character_dimensions')->nullOnDelete();
            $table->integer('points');
            $table->string('reason');
            $table->string('event_context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merit_points');
        Schema::dropIfExists('character_assessments');
        Schema::dropIfExists('character_dimensions');
    }
};
