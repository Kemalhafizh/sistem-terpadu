<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', 'unknown'])->default('unknown');
            $table->json('allergies')->nullable();                   // ["Debu", "Kacang", "Udang"]
            $table->json('chronic_conditions')->nullable();          // ["Asma", "Diabetes"]
            $table->json('medications')->nullable();                 // Obat rutin yang dikonsumsi
            $table->text('medical_notes')->nullable();               // Catatan medis khusus
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('insurance_number')->nullable();          // No. BPJS / asuransi
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->enum('vision_status', ['normal', 'minus', 'plus', 'silinder', 'unknown'])->default('unknown');
            $table->timestamps();
        });

        Schema::create('health_screenings', function (Blueprint $table) {
            $table->id();
            $table->string('title');                                 // "Screening Kesehatan Semester 1"
            $table->date('screening_date');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->json('target_classrooms')->nullable();           // [classroom_id, ...]
            $table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('health_screening_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_id')->constrained('health_screenings')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->string('vision_left')->nullable();               // "6/6", "6/12"
            $table->string('vision_right')->nullable();
            $table->string('dental_status')->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_screening_results');
        Schema::dropIfExists('health_screenings');
        Schema::dropIfExists('student_health_records');
    }
};
