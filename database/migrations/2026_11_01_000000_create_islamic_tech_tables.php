<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahsin_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('employees')->onDelete('cascade');
            $table->string('surah_name');
            $table->integer('start_ayah');
            $table->integer('end_ayah');
            $table->json('tajwid_errors')->nullable();
            $table->json('maqam_recognized')->nullable();
            $table->integer('fluency_score');
            $table->integer('makhraj_score');
            $table->text('teacher_notes')->nullable();
            $table->date('assessment_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahsin_records');
    }
};
