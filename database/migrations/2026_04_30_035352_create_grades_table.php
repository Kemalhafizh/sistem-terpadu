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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained();

            // Tipe Penilaian
            $table->enum('type', ['tugas', 'uh', 'pts', 'pas']);

            // Nilai Angka 0-100
            $table->decimal('score', 5, 2);

            // Deskripsi Kompetensi
            $table->text('description')->nullable();

            // Guru yang menginput nilai
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            // Mencegah duplikasi
            $table->unique(['student_id', 'subject_id', 'academic_year_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
